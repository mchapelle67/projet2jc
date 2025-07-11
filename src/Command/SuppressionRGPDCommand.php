<?php
namespace App\Command;

use App\Repository\RdvRepository;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SuppressionRGPDCommand extends Command
{
    protected static $defaultName = 'app:suppression-rgpd';

    protected function configure()
{
    $this
        ->setDescription('Supprime automatiquement les rendez-vous et les devis qui n \'ont pas été modifié pendant 3 ans.')
        ->setName(self::$defaultName);
}

    private $rdvRepository;
    private $devisRepository;
    private $entityManager;

    public function __construct(RdvRepository $rdvRepository, DevisRepository $devisRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->rdvRepository = $rdvRepository;
        $this->devisRepository = $devisRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        date_default_timezone_set('Europe/Paris');
        
        // on affiche un message de début d'exécution dans la console
        $output->writeln('--- Exécution à ' . (new \DateTime())->format('Y-m-d H:i:s') . ' ---');

        // on recupère la date des devis et des rendez-vous
        $allRdv = $this->rdvRepository->findAll();
        $allDevis = $this->devisRepository->findAll();
        $now = new \DateTime();
        $troisAns = $now->modify('-3 years');

        // on les supprime si la date de modification est superieur à 3 ans
        foreach ($allRdv as $rdv) {
            if ($rdv->getDateModification() < $troisAns) {
                $this->entityManager->remove($rdv);
                $output->writeln('Rendez-vous supprimé : ' . $rdv->getId() . ' - ' . $rdv->getDateRdv()->format('Y-m-d H:i:s'));
            }
        }
        // on les supprime si la date de modification est superieur à 3 ans
        foreach ($allDevis as $devis) {
            if ($devis->getDateModification() < $troisAns) {
                $this->entityManager->remove($devis);
                $output->writeln('Devis supprimé : ' . $devis->getId());
            }
        }

        $this->entityManager->flush();

        // on affiche un message de confirmation dans la console
        return Command::SUCCESS;
    }
}