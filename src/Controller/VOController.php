<?php

namespace App\Controller;

use App\Entity\VO;
use App\Entity\Photo;
use App\Form\VOTypeForm;
use App\Repository\VORepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
    public function addVO(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,
    // gestion de l'upload des photos
    #[Autowire('%kernel.project_dir%/public/uploads/vo')] string $photoDirectory): Response
    {
        // création du form
        $vehicule = new VO();
        $form = $this->createForm(VOTypeForm::class, $vehicule);

        // gestion de la requête
        $form->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // récupération du fichier photo
            /** @var UploadedFile $photoFile */
            $photosFile = $form->get('photos')->getData();
            
            // si une photo est uploadée
            if ($photosFile) {
                // on boucle pour récupérer toutes les photos
                foreach ($photosFile as $photoFile) {
                    // récupère le nom d'origine sans extension
                    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // transforme le nom en slug, nécessaire pour éviter les problèmes de sécurité, 
                    $safeFilename = $slugger->slug($originalFilename);
                    // on génère un nom de fichier unique en ajoutant un id pour éviter les conflits
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                    
                    // on déplace le fichier dans le répertoire des photos
                    try {
                        $photoFile->move($photoDirectory, $newFilename);
                    } catch (FileException $e) {
                        // on gère l'exception si quelque chose se passe pendant l'upload du fichier
                        $this->addFlash('error', 'Erreur lors de l\'upload de la photo : '.$e->getMessage());}
                        
                    // on crée une nouvelle entité Photo
                    $photo = new Photo();
                    $photo->setImg($newFilename);
                    $vehicule->addPhoto($photo);  
            }
                    
            // on récupère les données du formulaire
            $vehicule = $form->getData();
                    
            // on envois vers la bdd
            $entityManager->persist($vehicule);
            $entityManager->flush();
                    
            // puis on redirige vers la liste des véhicules d'occasion
            return $this->redirectToRoute('app_vo');
            }
        }   
        
        // affichage du formulaire
        return $this->render('vo/add.html.twig', [
            'form' => $form->createView(),
        ]);
        
    }
}