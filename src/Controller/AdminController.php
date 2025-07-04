<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\Devis;
use App\Model\SearchData;
use App\Form\SearchTypeForm;
use Doctrine\ORM\EntityManager;
use App\Repository\RdvRepository;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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

    #[Route('/admin/devis/historique', name: 'app_devis_historique')]
    public function historiqueDevis(DevisRepository $devisRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère les devis en bdd
        $devisList = $devisRepository->findBy([], ['date_devis' => 'DESC']);
        // on filtre les devis 
        $devisHistoriqueArray = array_filter($devisList, function (Devis $devis) {
            return $devis->getStatut() === 'Clôturé';
        });

        // on créer un resultat de recherche
        $searchData = new SearchData();

        // on créer le formulaire de recherche
        $searchForm = $this->createForm(SearchTypeForm::class, $searchData);
        $searchForm->handleRequest($request);

        // on créer la pagination
        $page = $request->query->getInt('page', 1);

        // si le formulaire est soumis et valide
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            // on récupère le paramètre dans l'url, si il n'existe pas on met '1' par défaut
            $searchData->page = $page;
            $devisResult = $devisRepository->findBySearch($searchData);

            return $this->render('admin/devis/historique.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devisHistorique' => $devisResult,
            'searchForm' => $searchForm->createView()
            ]);
        }

        $devisHistorique = $paginator->paginate($devisHistoriqueArray, $page, 20);

        return $this->render('admin/devis/historique.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devisHistorique' => $devisHistorique,
            'searchForm' => $searchForm->createView()
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

    #[Route('/admin/devis/{action}/{id}', name: 'gestion_devis_action', requirements: ['action' => 'delete|cloturer'])]
    public function deleteDevis(DevisRepository $devisRepository, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
         // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // récupérer l'id du devis
        $id = $request->attributes->get('id');
        // trouver le devis dans la base de données
        $devis = $devisRepository->find($id);

        if ($devis && $request->attributes->get('action') === 'delete') {
            // supprimer le devis de la base de données
            $entityManager->remove($devis);
            $entityManager->flush();

            // puis on redirige vers la liste des devis``
            $this->addFlash('success', 'Le devis a été supprimé avec succès.');
            return $this->redirectToRoute('app_devis_historique');
            
        } elseif ($devis && $request->attributes->get('action') === 'cloturer') {
            // si le devis existe, on change son statut
            $devis->setStatut('Clôturé');

            // on enregistre les modifications dans la base de données
            $entityManager->persist($devis);
            $entityManager->flush();

            // puis on redirige vers la liste des devis``
            $this->addFlash('success', 'Le devis a été traîté avec succès.');
            return $this->redirectToRoute('app_admin_devis');

        } else {
            // si l'action n'est pas reconnue, on affiche un message d'erreur
            $this->addFlash('error', 'Action inconnue.');
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
            return $rdv->getStatut() === 'En attente' && $rdv->getDateRdv() > new \DateTime();;
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

    #[Route('/admin/rdv/historique', name: 'app_rdv_historique')]
    public function historiqueRdv(RdvRepository $rdvRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on récupère les rdv en bdd
        $rdvList = $rdvRepository->findBy([], ['date_demande' => 'DESC']);

        // on filtre les rdv 
        $rdvHistoriqueArray = array_filter($rdvList, function (Rdv $rdv) {
            return (
                $rdv->getStatut() !== 'Confirmer' && $rdv->getStatut() !== 'En attente'
            );
        });

        // on créer un resultat de recherche
        $searchData = new SearchData();

        // on créer le formulaire de recherche
        $searchForm = $this->createForm(SearchTypeForm::class, $searchData);
        $searchForm->handleRequest($request);

        $page = $request->query->getInt('page', 1);

        // si le formulaire est soumis et valide
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            // on récupère le paramètre dans l'url, si il n'existe pas on met '1' par défaut
            $searchData->page = $page;
            $rdvResult = $rdvRepository->findBySearch($searchData);

            return $this->render('admin/rdv/historique.rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdvHistorique' => $rdvResult,
            'searchForm' => $searchForm->createView()
            ]);
        }

        $rdvHistorique = $paginator->paginate($rdvHistoriqueArray, $page, 20);

        return $this->render('admin/rdv/historique.rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdvHistorique' => $rdvHistorique,
            'searchForm' => $searchForm->createView()
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