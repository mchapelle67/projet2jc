<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\Vehicule;
use App\Form\DevisTypeForm;
use App\Service\ApiService;
use App\Form\VehiculeTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



final class VehiculeController extends AbstractController
{
    #[Route('/devis/client/vehicule', name: 'app_devis_vehicule')]
    public function devis(ApiService $apiService, Request $request, EntityManagerInterface $entityManager): Response
    {
        // on récupère toutes les marques de véhicules
        $marquesResponse = $apiService->getAllMakes();

        // on crée un forumlaire pour le devis
        $devis = new Devis(); 
        $vehicule = new Vehicule();
        $devisForm = $this->createForm(DevisTypeForm::class, $devis);
        $vehiculeForm = $this->createForm(VehiculeTypeForm::class, $vehicule);

        // on gère la requête
        $devisForm->handleRequest($request);
        $vehiculeForm->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($devisForm->isSubmitted() && $devisForm->isValid() && $vehiculeForm->isSubmitted() && $vehiculeForm->isValid()) {
                    
            // on récupère les données du formulaire
            $devis = $devisForm->getData();
            $vehicule = $vehiculeForm->getData();
                    
            // on envois vers la bdd
            $entityManager->persist($devis);
            $entityManager->flush();
            $entityManager->persist($vehicule);
            $entityManager->flush();
                    
            // puis on redirige vers la liste des véhicules d'occasion
            return $this->redirectToRoute('app_devis_vehicule', [
                'success' => true,
                'message' => 'Devis créé avec succès ! Nous reviendrons vers vous rapidement.',
            ]);
        } else {
            $this->addFlash('error', 'Erreur lors de la création de votre devis');
        }
        
        return $this->render('client/devis.html.twig', [
            'controller_name' => 'VehiculeController',
            'marques' => $marquesResponse,
            'devisForm' => $devisForm->createView(),     
            'vehiculeForm' => $vehiculeForm->createView()   
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
}
