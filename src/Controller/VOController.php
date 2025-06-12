<?php

namespace App\Controller;

use App\Entity\VO;
use App\Form\VOTypeForm;
use App\Repository\VORepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class VOController extends AbstractController
{
    #[Route('/vo', name: 'app_vo')]
    public function index(VORepository $voRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // créer liste des véhicules d'occasion et des photos associées
        $voList = $voRepository->findAll();
        $photos = [];
        foreach ($voList as $vo) {
            $photos[$vo->getId()] = $vo->getPhotos()->toArray();
        }

        return $this->render('vo/index.html.twig', [
            'controller_name' => 'VOController',
            'voList' => $voList,
            'photos' => $photos,
        ]);
    }

    #[Route('/vo/add', name: 'add_vo')]
    public function addVO(Request $request, EntityManagerInterface $entityManager): Response
    {
        // création du form
        $vehicule = new VO();
        $form = $this->createForm(VOTypeForm::class, $vehicule);

        // gestion de la requête
        $form->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // on récupère les données du formulaire
            $vehicule = $form->getData();
            // on envois vers la bdd
            $entityManager->persist($vehicule);
            $entityManager->flush();

            // puis on redirige vers la liste des véhicules d'occasion
            return $this->redirectToRoute('app_vo');
        }
        
        // affichage du formulaire
        return $this->render('vo/add.html.twig', [
            'form' => $form->createView(),
        ]);
        
    }
}
