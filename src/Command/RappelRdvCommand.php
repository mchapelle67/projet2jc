<?php

namespace App\Command; 

use App\Repository\RdvRepository;
use PHPMailer\PHPMailer\PHPMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RappelRdvCommand extends Command
{
    protected static $defaultName = 'app:rappel-rdv';

    protected function configure()
    {
    $this
        ->setDescription('Rappel automatiquement par mail les rdv à venir au client.')
        ->setName(self::$defaultName);
    }

    private $rdvRepository;
    private $entityManager;

    public function __construct(RdvRepository $rdvRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->rdvRepository = $rdvRepository;
        $this->entityManager = $entityManager;
    }

protected function execute(InputInterface $input, OutputInterface $output): int
{
    
    date_default_timezone_set('Europe/Paris');
    
    // on affiche un message de début d'exécution dans la console
    $output->writeln('--- Exécution à ' . (new \DateTime())->format('Y-m-d H:i:s') . ' ---');

    // Rendez-vous dans ~48h
    $rdvs = $this->rdvRepository->findBy(['rappel_rdv' => 0]);
    
    $now = new \DateTime();
    $in48h = (clone $now)->modify('+2 days');   

    $rappelEffectue = false;

    foreach ($rdvs as $rdv) {
        $dateRdv = $rdv->getDateRdv();
        if($dateRdv >=  $now && $dateRdv <= $in48h) {
            // on prepare le mail vers l'administrateur
            $mail = new PHPMailer(true);
        
            // paramètre du serveur SMTP
            $mail->SMTPDebug = 2;                                   // affiche les messages de debug (mettre à 0 en prod)
            $mail->Debugoutput = 'error_log';                         // pour que ça aille dans les logs PHP
            $mail->isSMTP();                                            // Simple Mail Transfer Protocol
            $mail->Host       = 'smtp.gmail.com';                     // configuration du serveur SMTP
            $mail->SMTPAuth   = true;                                   // active l'authentification SMTP
            $mail->Username   = 'manon.chp68@gmail.com';                     //SMTP username
            $mail->Password = $_SERVER['MAILER_PASSWORD'] ?? getenv('MAILER_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // sert à crypter la connexion
            $mail->Port       = 465;                                    // port du serveur SMTP
            
            
            // réglages de l'expéditeur et du destinataire
            $mail->setFrom('manon.chp68@gmail.com', '2jc');
            $mail->addAddress('manon.chp68@gmail.com');     
            
            // contenu du message
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Rappel de rendez-vous'; // Sujet du mail
            $mail->Body    = '<strong>Rappel de rendez-vous.</strong> <br>' .
            'Date du rendez-vous : ' . $dateRdv->format('d-m-Y à H:m') . '<br>' .
            'Prestation : ' . $rdv->getPrestation()->getNomPrestation() . '<br>' .
            'Un empêchement ? Contactez votre centre auto au 03 89 40 07 97.';
            $mail->AltBody = 'Ceci est le corps du message en texte brut pour les clients mail ne supportant pas le HTML';        
            
            // envoi du mail
            $mail->send();
            
            // mise à jour pour éviter les doublons
            $rdv->setRappelRdv(1);
            $this->entityManager->persist($rdv);
            $this->entityManager->flush();

            // on affiche un message de confirmation dans la console
            $output->writeln('Rappel de rdv effectué.');
            $rappelEffectue = true;
        }
    }

    if ($rappelEffectue) {
        return Command::SUCCESS;
    } else {
        $output->writeln('Aucun rappel de rdv à effectuer.');
        return Command::SUCCESS;
    }
}
}