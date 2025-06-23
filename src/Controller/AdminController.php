<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\Devis;
use Doctrine\ORM\EntityManager;
use App\Repository\RdvRepository;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AdminController extends AbstractController
{

// route pour la gestion des devis -------------------------------------------------

    #[Route('/admin/devis', name: 'app_admin_devis')]
    public function listDevis(DevisRepository $devisRepository): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère les devis en bdd
        $devisList = $devisRepository->findBy([], ['date_devis' => 'DESC']);
        // on filtre les devis en cours
        $devisEnCours = array_filter($devisList, function (Devis $devis) {
            return $devis->getStatut() === 'En cours';
        });

        return $this->render('admin/devis/devis.html.twig', [
            'controller_name' => 'AdminController',
            'devisEnCours' => $devisEnCours
        ]);
    }

    #[Route('/admin/devis/archives', name: 'app_devis_archives')]
    public function archivesDevis(DevisRepository $devisRepository): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère les devis en bdd
        $devisList = $devisRepository->findBy([], ['date_devis' => 'DESC']);
        // on filtre les devis 
        $devisArchives = array_filter($devisList, function (Devis $devis) {
            return $devis->getStatut() === 'Archivé';
        });

        return $this->render('admin/devis/archives.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devisArchives' => $devisArchives
        ]);
    }

    #[Route('/admin/devis/{id}', name: 'show_devis')]
    public function gestionDevis(DevisRepository $devisRepository, int $id): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère le devis en bdd
        $devis = $devisRepository->findOneBy(['id' => $id]);
        if (!$devis) {
            throw $this->createNotFoundException('Devis inexistant');
        }

        return $this->render('admin/devis/show.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devis' => $devis
        ]);
    }

    #[Route('/admin/devis/delete/{id}', name: 'delete_devis')]
    public function deleteDevis(DevisRepository $devisRepository, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
         // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // récupérer l'id du devis à supprimer
        $id = $request->attributes->get('id');
        // trouver le devis dans la base de données
        $devis = $devisRepository->find($id);
        if ($devis) {
            // supprimer le devis de la base de données
            $entityManager->remove($devis);
            $entityManager->flush();

            // puis on redirige vers la liste des devis``
            $this->addFlash('success', 'Le devis a été supprimé avec succès.');
            return $this->redirectToRoute('app_devis_archives');
            
        } elseif (!$devis) {
            // si le devis n'existe pas, on affiche un message d'erreur
            $this->addFlash('error', 'Le devis n\'a pas pu être supprimé.');
            return $this->redirectToRoute('app_devis_archives');
        }
    }

    #[Route('/admin/devis/update/{id}', name: 'update_devis')]
    public function updateDevis(DevisRepository $devisRepository, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
         // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // récupérer l'id du devis à traiter
        $id = $request->attributes->get('id');
        // trouver le devis dans la base de données
        $devis = $devisRepository->find($id);

        if ($devis) {
            // si le devis existe, on change son statut
            $devis->setStatut('Archivé');

            // on enregistre les modifications dans la base de données
            $entityManager->persist($devis);
            $entityManager->flush();

            // puis on redirige vers la liste des devis``
            $this->addFlash('success', 'Le devis a été traîtée avec succès.');
            return $this->redirectToRoute('app_devis_archives');
            
        } elseif (!$devis) {
            // si le devis n'existe pas, on affiche un message d'erreur
            $this->addFlash('error', 'Le devis n\'a pas pu être traîté.');
            return $this->redirectToRoute('app_admin_devis');
        }

         return $this->render('admin/devis/show.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devis' => $devis
        ]);
    }

// Route pour la gestion des rendez-vous ----------------------------------------------------

    #[Route('/admin/rdv', name: 'app_admin_rdv')]
    public function listRdv(RdvRepository $rdvRepository): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère les rdv en bdd
        $rdvList = $rdvRepository->findAll();
        // on filtre les rdv en cours
        $rdvEnCours = array_filter($rdvList, function (Rdv $rdv) {
            return $rdv->getStatut() === 'En attente';
        });
        // on filtre les rdv confirmés pas encore passés
        $rdvConfirmer = array_filter($rdvList, function (Rdv $rdv) {
            return $rdv->getStatut() === 'Confirmer' && $rdv->getDateRdv() > new \DateTime();
        });

        return $this->render('admin/rdv/rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdvEnCours' => $rdvEnCours,
            'rdvConfirmer' => $rdvConfirmer
        ]);
    }

    #[Route('/admin/rdv/archives', name: 'app_rdv_archives')]
    public function archivesRdv(RdvRepository $rdvRepository): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère les rdv en bdd
        $rdvList = $rdvRepository->findBy([], ['date_demande' => 'DESC']);

        // on filtre les rdv 
        $rdvArchives = array_filter($rdvList, function (Rdv $rdv) {
            return (
                ($rdv->getStatut() === 'Confirmer' && $rdv->getDateRdv() < new \DateTime())
                || $rdv->getStatut() === 'Refuser' || $rdv->getStatut() === 'Annuler'
            );
        });

        return $this->render('admin/rdv/archives.rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdvArchives' => $rdvArchives
        ]);
    }

    #[Route('/admin/rdv/{id}', name: 'show_rdv')]
    public function showRdv(RdvRepository $rdvRepository, int $id): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère le devis en bdd
        $rdv = $rdvRepository->findOneBy(['id' => $id]);
        if (!$rdv) {
            throw $this->createNotFoundException('Rendez-vous inexistant');
        }

        return $this->render('admin/rdv/show.rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdv' => $rdv
        ]);
    }

    #[Route('/admin/rdv/{action}/{id}', name: 'gestion_rdv_action', requirements: ['action' => 'accept|decline|cancel'])]
    public function changeRdvStatut(RdvRepository $rdvRepository, string $action, int $id, EntityManagerInterface $entityManager): Response 
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // récupérer l'id du rendez-vous à traiter
        $rdv = $rdvRepository->find($id);
        if (!$rdv) {
            throw $this->createNotFoundException('Rendez-vous inexistant');
        }

        if ($action === 'accept') {
            $rdv->setStatut('Confirmer');
            $this->addFlash('success', 'Le rendez-vous a été accepté avec succès.');
        } elseif ($action === 'decline') {
            $rdv->setStatut('Refuser');
            $this->addFlash('success', 'Le rendez-vous a bien été refusé.');
        } elseif ($action === 'cancel') {
            $rdv->setStatut('Annuler');
            $this->addFlash('success', 'Le rendez-vous a été annulé avec succès.');
        } else {    
            throw $this->createNotFoundException('Action inconnue');
        }

    $entityManager->flush();

    return $this->redirectToRoute('app_admin_rdv');
}
}