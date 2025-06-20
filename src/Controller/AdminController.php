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
        // on filtre les devis en cours
        $devisArchives = array_filter($devisList, function (Devis $devis) {
            return $devis->getStatut() === 'Archivé';
        });

        return $this->render('admin/devis/archives.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devisArchives' => $devisArchives
        ]);
    }

    #[Route('/admin/devis/{id}', name: 'gestion_devis')]
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

    #[Route('/admin/rdv', name: 'app_admin_rdv')]
    public function listRdv(RdvRepository $rdvRepository): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère les devis en bdd
        $rdvList = $rdvRepository->findAll();
        // on filtre les devis en cours
        $rdvEnCours = array_filter($rdvList, function (Rdv $rdv) {
            return $rdv->getStatut() === 'En cours';
        });

        return $this->render('admin/rdv/rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdvEnCours' => $rdvEnCours
        ]);
    }
}