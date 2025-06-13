<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\ApiService;



final class VehiculeController extends AbstractController
{
    #[Route('/api/vehicule', name: 'api_vehicule')]
    public function index(ApiService $apiService, Request $request): Response
    {
        // on récupère toutes les marques de véhicules
        $marquesResponse = $apiService->getAllMakes();

        return $this->render('vehicule/index.html.twig', [
            'controller_name' => 'VehiculeController',
            'marques' => $marquesResponse        ]);
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
