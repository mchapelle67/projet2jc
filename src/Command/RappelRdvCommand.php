<?php

namespace App\Command; 

use App\Service\MailService;
use App\Repository\RdvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RappelRdvCommand extends Command
{
    // php bin/console app:rappel-rdv
    protected static $defaultName = 'app:rappel-rdv';

    protected function configure()
    {
    $this
        ->setDescription('Rappel automatiquement par mail les rdv à venir au client.')
        ->setName(self::$defaultName);
    }

    private $rdvRepository;
    private $entityManager;

    public function __construct(RdvRepository $rdvRepository, EntityManagerInterface $entityManager, MailService $mail)
    {
        parent::__construct();
        $this->rdvRepository = $rdvRepository;
        $this->entityManager = $entityManager;
        $this->mail = $mail;
    }

protected function execute(InputInterface $input, OutputInterface $output): int
{
    
    date_default_timezone_set('Europe/Paris');
    
    // on affiche un message de début d'exécution dans la console
    $output->writeln('--- Exécution à ' . (new \DateTime())->format('Y-m-d H:i:s') . ' ---');

    // Rendez-vous dans ~48h
    $rdvs = $this->rdvRepository->findBy([
        'rappel_rdv' => 0,
        'statut' => 'confirmer'
    ]);
    
    $now = new \DateTime();
    $in48h = (clone $now)->modify('+2 days');   

    $rappelEffectue = false;

    foreach ($rdvs as $rdv) {
        $dateRdv = $rdv->getDateRdv();
        if($dateRdv >=  $now && $dateRdv <= $in48h) {            
            // contenu du message
            $mailSubject = 'Rappel de rendez-vous'; 
            $mailBody    = '<p>' . "Bonjour " . $rdv->getNom() . ' ' . $rdv->getPrenom() . "," . '</p>' .
                            '<p>' . "Vous avez rendez-vous dans notre centre 2JC automobiles situé au " . '<strong>' . "18 route de Thann, 68130 ALTKIRCH " . '</strong>' . "le : " . $dateRdv->format('d-m-Y à H:m') . " pour la prestation suivante : " . '<strong>' . $rdv->getPrestation()->getNomPrestation() . ". " . '</strong><br>' .
                            '<p> ' . "Pour tout desistement veuillez nous contacter par téléphone au 03 89 40 07 97." . '<br>' .
                            "Nous restons à votre écoute pour tout renseignement supplémentaire." . '</p>' .
                            '<p>' . "Cordialement," . ' <br>' . 
                            "L'équipe de 2JC Automobiles" . '</p>'; 
            $mailAltBody = 'Bonjour, vous avez rendez-vous dans notre centre automobiles 2JC situé à Altkirch.';
            $mailClient = $rdv->getEmail();        

            // envoi du mail
            $this->mail->sendMail($mailSubject, $mailBody, $mailAltBody, $mailClient); 
            
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