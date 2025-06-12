<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VOController extends AbstractController
{
    #[Route('/vo', name: 'app_vo')]
    public function index(): Response
    {
        return $this->render('vo/index.html.twig', [
            'controller_name' => 'VOController',
        ]);
    }
}
