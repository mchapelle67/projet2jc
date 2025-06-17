<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\Devis;
use App\Form\RdvTypeForm;
use App\Form\DevisTypeForm;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ClientController extends AbstractController
{
    #[Route('/devis/client', name: 'app_devis_client')]
    public function devis(ApiService $apiService, Request $request, EntityManagerInterface $entityManager): Response
    {
        // on récupère toutes les marques de véhicules
        $marquesResponse = $apiService->getAllMakes();

        // on crée un forumlaire pour le devis
        $devis = new Devis(); 
        $devisForm = $this->createForm(DevisTypeForm::class, $devis);

        // on gère la requête
        $devisForm->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($devisForm->isSubmitted() && $devisForm->isValid()) {
                    
            // on récupère les données du formulaire
            $devis = $devisForm->getData();

            // on créer automatiquement un statut 
            $devis->setStatut('En cours');

            // on gère les erreurs potentielles
            if (!$devis->getVehicule()->getMarque()) {
                $this->addFlash('error', 'Veuillez sélectionner une marque de véhicule.');
                return $this->render('client/devis.html.twig', [
                    'controller_name' => 'ClientController',
                    'marques' => $marquesResponse,
                    'devisForm' => $devisForm->createView(),
                ]);
            }
            if (!$devis->getVehicule()->getModele()) {
                $this->addFlash('error', 'Veuillez sélectionner un modèle de véhicule.');
                return $this->render('client/devis.html.twig', [
                    'controller_name' => 'ClientController',
                    'marques' => $marquesResponse,
                    'devisForm' => $devisForm->createView(),
                ]);
            }
        
            // on envois vers la bdd
            $entityManager->persist($devis);
            $entityManager->flush();
                    
            // puis on redirige vers la liste des véhicules d'occasion
            return $this->redirectToRoute('app_devis_client', [
                'success' => true,
                'message' => 'Devis créé avec succès ! Nous reviendrons vers vous rapidement.',
            ]);
        } 
        else {
            $this->addFlash('error', 'Erreur lors de la création de votre devis');
        }
        
        return $this->render('client/devis.html.twig', [
            'controller_name' => 'ClientController',
            'marques' => $marquesResponse,
            'devisForm' => $devisForm->createView(),     
        ]);
    }

    #[Route('/api/modeles', name: 'api_modeles')]
    public function apiModeles(ApiService $apiService, Request $request): Response
    {
        $marque = $request->query->get('marque');
        $modeles = [];
        if ($marque) {
            $modeles = $apiService->getModelsByMake($marque);
        }

        return $this->json($modeles);
    }

    #[Route('/rdv/client', name: 'app_rdv_client')]
    public function rdv(ApiService $apiService, Request $request, EntityManagerInterface $entityManager): Response
    {
        // on récupère toutes les marques de véhicules
        $marquesResponse = $apiService->getAllMakes();

        // on crée un forumlaire pour la prise de rdv
        $rdv = new Rdv(); 
        $rdvForm = $this->createForm(RdvTypeForm::class, $rdv);

        // on gère la requête
        $rdvForm->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($rdvForm->isSubmitted() && $rdvForm->isValid()) {
                    
            // on récupère les données du formulaire
            $rdv = $rdvForm->getData();

            // on créer automatiquement un statut 
            $rdv->setStatut('En cours');

            // on gère les erreurs potentielles
            if (!$rdv->getVehicule()->getMarque()) {
                $this->addFlash('error', 'Veuillez sélectionner une marque de véhicule.');
                return $this->render('client/rdv.html.twig', [
                    'controller_name' => 'ClientController',
                    'marques' => $marquesResponse,
                    'rdvForm' => $rdvForm->createView(),
                ]);
            }
            if (!$rdv->getVehicule()->getModele()) {
                $this->addFlash('error', 'Veuillez sélectionner un modèle de véhicule.');
                return $this->render('client/rdv.html.twig', [
                    'controller_name' => 'ClientController',
                    'marques' => $marquesResponse,
                    'rdvForm' => $rdvForm->createView(),
                ]);
            }
        
            // on envois vers la bdd
            $entityManager->persist($rdv);
            $entityManager->flush();
                    
            // puis on redirige vers la liste des véhicules d'occasion
            return $this->redirectToRoute('app_rdv_client', [
                'success' => true,
                'message' => 'Votre demande de rendez-vous a bien été prise en compte, nous reviendrons vers vous rapidement.',
            ]);
        } 
        else {
            $this->addFlash('error', 'Erreur lors de la création de votre demande de rendez-vous');
        }
        
        return $this->render('client/rdv.html.twig', [
            'controller_name' => 'ClientController',
            'marques' => $marquesResponse,
            'rdvForm' => $rdvForm->createView(),     
        ]);
    }
}
