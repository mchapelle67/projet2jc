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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/vehicules-occasions')]
final class VOController extends AbstractController
{

// VO partie client -----------------------------------------------------
    #[Route('/liste', name: 'app_vo')]
    public function index(VORepository $voRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // créer liste des véhicules d'occasion et des photos associées
        $voList = $voRepository->findBy([], ['date_modification' => 'DESC']);

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

// VO partie admin ----------------------------------------------------
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/add', name: 'add_vo')]
    public function addVO(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,
    // gestion de l'upload des photos
    #[Autowire('%kernel.project_dir%/public/uploads/vo')] string $photoDirectory): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // création du form
        $vehicule = new VO();
        $form = $this->createForm(VOTypeForm::class, $vehicule);

        // gestion de la requête
        $form->handleRequest($request);

        // on choisit les types d'extensions autorisées
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // récupération du fichier photo
            /** @var UploadedFile $photoFile */
            $photosFile = $form->get('photos')->getData();
            
            // si une photo est uploadée
            if ($photosFile) {
                // on boucle pour récupérer toutes les photos
                foreach ($photosFile as $photoFile) {
                    // on vérifie l'extension du fichier
                    $extension = strtolower($photoFile->guessExtension());
                    if (in_array($extension, $allowedExtensions)) {
                        // hash le nom en slug, nécessaire pour éviter les problèmes de sécurité
                        $safeFilename = hash_file('sha256', $photoFile->getPathname());
                        // on génère un nom de fichier unique en ajoutant un id pour éviter les conflits
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;
                        
                        // on déplace le fichier dans le répertoire des photos
                        try {
                            $photoFile->move($photoDirectory, $newFilename);
                        } catch (FileException $e) {
                            // on gère l'exception si quelque chose se passe pendant l'upload du fichier
                            $this->addFlash('error', 'Erreur lors de l\'upload de la photo : '.$e->getMessage());
                        }
                        
                        // on crée une nouvelle entité Photo
                        $photo = new Photo();
                        $photo->setImg($newFilename);
                        $vehicule->addPhoto($photo);  
                    } else {
                        $this->addFlash('error', 'Extension de fichier non autorisée : '.$extension);
                    }
                }
                    
                // on récupère les données du formulaire
                $vehicule = $form->getData();
                        
                // on envois vers la bdd
                $entityManager->persist($vehicule);
                $entityManager->flush();
                        
                // puis on redirige vers la liste des véhicules d'occasion
                $this->addFlash("success", "Le véhicule a été ajouté avec succès.");
                return $this->redirectToRoute('app_vo');

            } elseif ($form->isSubmitted() && !$form->isValid()) {
            // si le formulaire n'est pas soumis ou n'est pas valide, on ajoute un message flash
            $this->addFlash('error', 'Veuillez remplir tout les champs requis.');
            }
        }
        
        // affichage du formulaire
        return $this->render('vo/add.html.twig', [
            'form' => $form->createView(),
        ]);
        
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/edit/{id}', name: 'edit_vo')]
    public function editVO(Request $request, EntityManagerInterface $entityManager, VORepository $voRepository, SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads/vo')] string $photoDirectory): Response
    {

        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // récupérer l'id du véhicule 
        $id = $request->attributes->get('id');
        // trouver le véhicule dans la base de données
        $vehicule = $voRepository->find($id);

        // si le véhicule n'existe pas, rediriger vers la liste des véhicules d'occasion
        if (!$vehicule) {
            return $this->redirectToRoute('app_vo');
        }
        
        // créer le formulaire avec les données du véhicule
        $form = $this->createForm(VOTypeForm::class, $vehicule);
        
        // gérer la requête
        $form->handleRequest($request);

        // récupérer les photos du véhicule
        $photos = $vehicule->getPhotos()->toArray();
    
        // on choisit les types d'extensions autorisées
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // récupération du fichier photo
            /** @var UploadedFile $photoFile */
            $photosFile = $form->get('photos')->getData();
            
            // si une photo est uploadée
            if ($photosFile) {
            // on boucle pour récupérer toutes les photos
                foreach ($photosFile as $photoFile) {
                    // on vérifie l'extension du fichier
                    $extension = strtolower($photoFile->guessExtension());
                    if (in_array($extension, $allowedExtensions)) {
                        // hash le nom en slug, nécessaire pour éviter les problèmes de sécurité
                        $safeFilename = hash_file('sha256', $photoFile->getPathname());
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
                        
                    } else {
                        $this->addFlash('error', 'Extension de fichier non autorisée : '.$extension);
                    }
                }

                // on enregistre les modifications dans la base de données
                $entityManager->persist($vehicule);
                $entityManager->flush();

                // puis on redirige vers la liste des véhicules d'occasion
                $this->addFlash('success', 'Le véhicule a été modifié avec succès.');
                return $this->redirectToRoute('app_vo');

            } elseif ($form->isSubmitted() && !$form->isValid()) {
                // si le formulaire n'est pas soumis ou n'est pas valide, on ajoute un message flash
                $this->addFlash('error', 'Erreur lors de la modification du vehicule.');
            }    
        }
        
        // affichage du formulaire d'édition
        return $this->render('vo/edit.html.twig', [
            'form' => $form->createView(),
            'vehicule' => $vehicule,
            'photos' => $photos,
        ]);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{voId}/photo/{photoId}/delete', name: 'delete_photo_vo')]
    public function deletePhotoVO(Request $request, EntityManagerInterface $entityManager, VORepository $voRepository, int $photoId, int $voId): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on cherche les id 
        $vehicule = $entityManager->getRepository(VO::class)->find($voId);
        $photo = $entityManager->getRepository(Photo::class)->find($photoId);

        // si le véhicule n'existe pas, rediriger vers la liste des véhicules d'occasion
        if (!$vehicule) {
            return $this->redirectToRoute('app_vo');
        }

        // on retire la photo de la collection du véhicule, grâce à orphanRemoval elle se supprimera toute seule de la bdd 
        $vehicule->removePhoto($photo);
        // enregistrer les modifications dans la base de données
        $entityManager->flush();

        // puis on retourne sur l'edit de ce véhicule
        return $this->redirectToRoute('edit_vo', [
            'id' => $voId,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'delete_vo')]
    public function deleteVO(Request $request, EntityManagerInterface $entityManager, VORepository $voRepository): Response
    {
        // on verifie que l'utilisateur a le rôle admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // récupérer l'id du véhicule
        $id = $request->attributes->get('id');
        // trouver le véhicule dans la base de données
        $vehicule = $voRepository->find($id);

        // si le véhicule n'existe pas, rediriger vers la liste des véhicules d'occasion
        if (!$vehicule) {
            $this->addFlash('error', 'Le véhicule n\'existe pas.');
            return $this->redirectToRoute('app_vo');
        }

        // supprimer le véhicule de la base de données
        $entityManager->remove($vehicule);
        $entityManager->flush();
        
        $this->addFlash('success', 'Le véhicule a été supprimé avec succès.');
        return $this->redirectToRoute('app_vo');
    }
}