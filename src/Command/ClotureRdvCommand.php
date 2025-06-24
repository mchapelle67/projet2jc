<?php
namespace App\Command;

use App\Repository\RdvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClotureRdvCommand extends Command
{
    protected static $defaultName = 'app:cloture-rdv';

    protected function configure()
{
    $this
        ->setDescription('Clôture automatiquement les rendez-vous passés.')
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

    // permet de cloturer automatiquement un rdv lorsque la date de celui-ci est dépassée
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // on recupère les rdv avec le statut "Confirmer" et la date actuelle
        $rdvHistorique = $this->rdvRepository->findBy(['statut' => 'Confirmer']);
        $now = new \DateTime();

        // on les clôture une fois la date de rdv depassée
        foreach ($rdvHistorique as $rdv) {
            if ($rdv->getDateRdv() < $now) {
                $rdv->setStatut('Clôturé');
            }
        }
        $this->entityManager->flush();

        // on affiche un message de confirmation dans la console
        $output->writeln('Clôture des rendez-vous effectuée.');
        return Command::SUCCESS;
    }
}