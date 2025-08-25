<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\Devis;
use App\Model\SearchData;
use App\Form\SearchTypeForm;
use App\Service\MailService;
use App\Form\EditRdvTypeForm;
use App\Repository\RdvRepository;
use App\Repository\DevisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
final class AdminController extends AbstractController
{

// route pour la gestion des devis -------------------------------------------------
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/devis', name: 'app_admin_devis')]
    public function listeDevis(DevisRepository $devisRepository): Response
    {
        // on récupère les devis en bdd
        $devisList = $devisRepository->findAll();
        // on filtre les devis en cours
        $devisEnCours = array_filter($devisList, function (Devis $devis) {
            return $devis->getStatut() === 'En cours';
        });

        return $this->render('admin/devis/liste.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devisEnCours' => $devisEnCours
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/devis/historique', name: 'app_devis_historique')]
    public function historiqueDevis(DevisRepository $devisRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // on récupère les devis en bdd
        $devisList = $devisRepository->findBy([], ['date_devis' => 'DESC']);

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
            'searchForm' => $searchForm->createView(),
        ]);
        }

        $devisHistorique = $paginator->paginate($devisList, $page, 20);

        return $this->render('admin/devis/historique.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devisHistorique' => $devisHistorique,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/devis/{slug}', name: 'show_devis')]
    public function showDevis(DevisRepository $devisRepository, string $slug): Response
    {
        // on récupère le devis en bdd
        $devis = $devisRepository->findOneBy(['slug' => $slug]);
        if (!$devis) {
            throw $this->createNotFoundException('Devis inexistant');
        }

        return $this->render('admin/devis/show.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devis' => $devis
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/devis/{action}/{id}', name: 'gestion_devis_action', requirements: ['action' => 'decline|delete|cloturer'])]
    public function gestionDevis(string $action, DevisRepository $devisRepository, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // récupérer l'id du devis
        $id = $request->attributes->get('id');
        // trouver le devis dans la base de données
        $devis = $devisRepository->find($id);

        if ($action === 'delete') {
            // supprimer le devis de la base de données
            $entityManager->remove($devis);
            $entityManager->flush();

            // puis on redirige vers la liste des devis``
            $this->addFlash('success', 'Le devis a été supprimé avec succès.');
            return $this->redirectToRoute('app_admin_devis');
            
        } elseif ($action === 'cloturer') {
            // si le devis existe, on change son statut
            $devis->setStatut('Clôturé');

            // on enregistre les modifications dans la base de données
            $entityManager->persist($devis);
            $entityManager->flush();

            // puis on redirige vers la liste des devis``
            $this->addFlash('success', 'Le devis a été traîté avec succès.');
            return $this->redirectToRoute('app_admin_devis');

        } elseif ($action === 'decline') {
            // si le devis existe, on change son statut
            $devis->setStatut('Clôturé');

            // on enregistre les modifications dans la base de données
            $entityManager->persist($devis);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le devis a été traîté avec succès.');
            return $this->render('admin/gestionMail.html.twig', [
            'controller_name' => 'AdminController',
            'action' => 'devisDecline',
            'devis' => $devis,
            'id' => $devis->getId()
        ]);
        } else {
            // si l'action n'est pas reconnue, on affiche un message d'erreur
            $this->addFlash('error', 'Action inconnue.');
        }

        return $this->redirectToRoute('app_admin_devis');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/devis/update/{id}', name: 'app_admin_devis_update')]
    public function updateDevis(int $id, DevisRepository $devisRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $id = $request->attributes->get('id');
        $devis = $devisRepository->find($id);

        if (!$devis) {
            throw $this->createNotFoundException('Devis inexistant');
        }
        
        $devis->setStatut('En cours');

        $entityManager->persist($devis);
        $entityManager->flush();
            
        
        return $this->render('admin/devis/show.devis.html.twig', [
            'controller_name' => 'AdminController',
            'devis' => $devis
        ]);
    }

// route pour la gestion des rendez-vous ----------------------------------------------------
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/rdv', name: 'app_admin_rdv')]
    public function listeRdv(RdvRepository $rdvRepository): Response
    {
        // on récupère les rdv en bdd
        $rdvList = $rdvRepository->findAll();
        // on filtre les rdv en cours
        $rdvEnCours = array_filter($rdvList, function (Rdv $rdv) {
            return $rdv->getStatut() === 'En attente';
        });
        // on filtre les rdv confirmés pas encore passés
        $rdvConfirmer = array_filter($rdvList, function (Rdv $rdv) {
            return $rdv->getStatut() === 'Confirmer';
        });

        return $this->render('admin/rdv/liste.rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdvEnCours' => $rdvEnCours,
            'rdvConfirmer' => $rdvConfirmer
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/rdv/historique', name: 'app_rdv_historique')]
    public function historiqueRdv(RdvRepository $rdvRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // on récupère les rdv en bdd
        $rdvList = $rdvRepository->findBy([], ['date_demande' => 'DESC']);

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

        $rdvHistorique = $paginator->paginate($rdvList, $page, 20);

        return $this->render('admin/rdv/historique.rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdvHistorique' => $rdvHistorique,
            'searchForm' => $searchForm->createView()
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/rdv/{slug}', name: 'show_rdv')]
    public function showRdv(RdvRepository $rdvRepository, string $slug): Response
    {
        // on récupère le devis en bdd
        $rdv = $rdvRepository->findOneBy(['slug' => $slug]);
        if (!$rdv) {
            throw $this->createNotFoundException('Rendez-vous inexistant');
        }

        // on récupère la date d'ajourd'hui
        $now = new \DateTime();

        return $this->render('admin/rdv/show.rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdv' => $rdv,
            'now' => $now
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/rdv/{action}/{id}', name: 'gestion_rdv_action', requirements: ['action' => 'accept|decline|cancel|delete'])]
    public function gestionRdv(RdvRepository $rdvRepository, string $action, int $id, EntityManagerInterface $entityManager): Response 
    {
        // récupérer l'id du rendez-vous à traiter
        $rdv = $rdvRepository->find($id);
        if (!$rdv) {
            throw $this->createNotFoundException('Rendez-vous inexistant');
        }

        if ($action === 'accept') {
            $rdv->setStatut('Confirmer');
            $entityManager->flush();
            $this->addFlash('success', 'Le rendez-vous a été accepté avec succès.');
            return $this->redirectToRoute('gestion_mail', [
                'action' => 'rdvAccept',
                'id' => $rdv->getId()
            ]);

        } elseif ($action === 'decline') {
            $rdv->setStatut('Refuser');
            $this->addFlash('success', 'Le rendez-vous a bien été refusé.');
       
        } elseif ($action === 'cancel') {
            $rdv->setStatut('Annuler');
            $this->addFlash('success', 'Le rendez-vous a été annulé avec succès.');
        
        } elseif ($action === 'delete') {
            $entityManager->remove($rdv);
            $entityManager->flush();
            $this->addFlash('success', 'Le rendez-vous a été supprimé avec succès.');
            return $this->redirectToRoute('app_admin_rdv');

        } else {    
            throw $this->createNotFoundException('Action inconnue');
        }

        $entityManager->flush();

        return $this->render('admin/gestionMail.html.twig', [
            'controller_name' => 'AdminController',
            'action' => 'rdv' . ucfirst($action),
            'rdv' => $rdv,
            'id' => $rdv->getId()
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/rdv/update/{id}', name: 'app_admin_rdv_update')]
    public function updateStatutRdv(RdvRepository $rdvRepository, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $id = $request->attributes->get('id');
        $rdv = $rdvRepository->find($id);
        if (!$rdv) {
            throw $this->createNotFoundException('Rdv inexistant');
        }
        
        $now = new \DateTime();
        
        $rdv->setStatut('En attente');
        $entityManager->persist($rdv);
        $entityManager->flush();
        
        return $this->render('admin/rdv/show.rdv.html.twig', [
            'controller_name' => 'AdminController',
            'rdv' => $rdv, 
            'now' => $now
        ]);
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/calendar/rdv', name: 'app_admin_rdv_calendar')]
    public function calendrier(RdvRepository $rdvRepository): Response
    {
        $rdvs = $rdvRepository->findBy(['statut' => 'Confirmer']);
        $events = [];
        foreach ($rdvs as $rdv) {
            $events[] = [
                'title' => $rdv->getPrestation()->getNomPrestation(),
                'start' => $rdv->getDateRdv()->format('Y-m-d\TH:i:s'),
                'url' => $this->generateUrl('show_rdv', ['slug' => $rdv->getSlug()])
            ];
        }

        return $this->render('admin/rdv/calendar.html.twig', [
            'controller_name' => 'AdminController',
            'events' => $events
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/rdv/edit/{id}', name: 'edit_rdv')]
    public function editRdv(Request $request, EntityManagerInterface $entityManager, RdvRepository $rdvRepository): Response
    {
        // trouver le rdv dans la base de données
        $id = $request->attributes->get('id');
        $rdv = $rdvRepository->find($id);
        $slug = $rdv->getSlug();

        if (!$rdv) {
            return $this->redirectToRoute('app_admin_rdv');
        }
        
        // créer le formulaire avec les données du véhicule
        $rdvForm = $this->createForm(EditRdvTypeForm::class, $rdv);
        
        // gérer la requête
        $rdvForm->handleRequest($request);

        if ($rdvForm->isSubmitted() && $rdvForm->isValid()) {

            // on vérifie que la date du rendez-vous n'est pas un dimanche ou un lundi au cas où javascript n'est pas prit en compte par le navigateur 
            $date = $rdv->getDateRdv();
            if (in_array((int) $date->format('w'), [0, 1])) {
                $this->addFlash('error', 'Les rendez-vous ne sont pas possibles les dimanches et lundis.');
                return $this->redirectToRoute('edit_rdv', ['slug' => $rdv->getSlug()]);
            }
       
            // on enregistre les modifications dans la base de données
            $entityManager->persist($rdvForm->getData());
            $entityManager->flush();

            $this->addFlash('success', 'Les modifications ont bien été prises en comptes.');
            return $this->redirectToRoute('show_rdv', ['slug' => $rdv->getSlug()]);

            } elseif ($rdvForm->isSubmitted() && !$rdvForm->isValid()) {
                $this->addFlash('error', 'Erreur lors de la modification du rendez-vous.');
            }
        
    
        // affichage du formulaire d'édition
        return $this->render('admin/rdv/editRdv.html.twig', [
            'controller_name' => 'AdminController',
            'form' => $rdvForm->createView(),
            'rdv' => $rdv, 
        ]);
    }

// Route pour la gestion des mails ----------------------------------------------------
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/gestion/mail/{action}/{id}', name: 'gestion_mail', requirements: ['action' => 'rdvAccept|rdvDecline|rdvCancel|devisDecline'])]
        public function gestionMail(MailService $mail, RdvRepository $rdvRepository, DevisRepository $devisRepository, string $action, int $id): Response 
        {
            // on prépare le corps du mail selon l'action 
            if ($action === 'rdvAccept') {
                $rdv = $rdvRepository->find($id);
                if (!$rdv) {
                    throw $this->createNotFoundException('Rendez-vous inexistant');
                }
                $body = '<p>' . "Bonjour " . $rdv->getNom() . ' ' . $rdv->getPrenom() . "," . '</p>' .
                        '<p>' . "Nous vous confirmons votre demande de rendez-vous du : " . '<strong>' . $rdv->getDateRdv()->format('d/m/Y à H:i') . '</strong>' . " pour la prestation suivante : " .'<strong>'. $rdv->getPrestation()->getNomPrestation() . ". " . '</strong></p>' .
                        '<p> ' . "Rendez-vous dans votre centre 2JC Automobiles à l'adresse suivante : " . '<strong>' . "18 route de Thann, 68130 ALTKIRCH." . '</strong>' . '</p>' .
                        "Pour tout désistement, veuillez nous contacter par téléphone au 03 89 40 07 97." . '<br>' .
                        "Nous restons à votre disposition pour tout renseignements supplémentaire." . '</p>' .
                        '<p>' . "Cordialement," . ' <br>' . 
                        "L'équipe de 2JC Automobiles" . '</p>';

            } elseif ($action === 'rdvDecline') {
                $rdv = $rdvRepository->find($id);
                if (!$rdv) {
                    throw $this->createNotFoundException('Rendez-vous inexistant');
                }
                $body = '<p>' . "Bonjour " . $rdv->getNom() . ' ' . $rdv->getPrenom() . "," . '</p>' .
                        '<p>' . "Suite à votre demande de rendez-vous pour le : " . '<strong>' . $rdv->getDateRdv()->format('d/m/Y à H:i') . '</strong>' . " pour la prestation suivante : " .'<strong>'. $rdv->getPrestation()->getNomPrestation() . ", " . '</strong><br>' .
                        "nous avons le regret de vous informer que nous ne pourrons pas honnorer votre demande à la date souhaitée." . '</p>' .
                        '<p> ' . "Nous vous invitons à prendre un nouveau rendez-vous en ligne sur notre site internet ou par téléphone au 03 89 40 07 97." . '<br>' .
                        "Nous vous remercions de votre compréhension et restons à votre disposition pour toute question." . '</p>' .
                        '<p>' . "Cordialement," . ' <br>' . 
                        "L'équipe de 2JC Automobiles" . '</p>';

            } elseif ($action === 'rdvCancel') {
                $rdv = $rdvRepository->find($id);
                if (!$rdv) {
                    throw $this->createNotFoundException('Rendez-vous inexistant');
                }
                $body = '<p>' . "Bonjour " . $rdv->getNom() . ' ' . $rdv->getPrenom() . "," . '</p>' .
                        '<p>' . "Suite à votre demande de rendez-vous pour le : " . '<strong>' . $rdv->getDateRdv()->format('d/m/Y à H:i') . '</strong>' . " pour la prestation suivante : " .'<strong>'. $rdv->getPrestation()->getNomPrestation() . ", " . '</strong><br>' .
                        "nous avons le regret de vous informer que nous ne pourrons pas honnorer votre demande à la date souhaitée et que nous sommes contraints d'annuler la prestation." . '</p>' .
                        '<p> ' . "Nous vous invitons à prendre un nouveau rendez-vous en ligne sur notre site internet ou par téléphone au 03 89 40 07 97." . '<br>' .
                        "Nous vous remercions de votre compréhension et restons à votre disposition pour toute question." . '</p>' .
                        '<p>' . "Cordialement," . ' <br>' . 
                        "L'équipe de 2JC Automobiles" . '</p>';

            } elseif ($action === 'devisDecline') {
                $devis = $devisRepository->find($id);
                if (!$devis) {
                    throw $this->createNotFoundException('Devis inexistant');
                }
                $body = '<p>' . "Bonjour " . $devis->getNom() . ' ' . $devis->getPrenom() . "," . '</p>' .
                        '<p>' . "Suite à votre demande de devis pour la prestation suivante : " .'<strong>'. $devis->getPrestation()->getNomPrestation() . ", " . '</strong><br>' .
                        "nous avons le regret de vous informer que nous ne pouvons pas donner suite à votre demande." . '</p>' .
                        '<p> ' . "Pour tout renseignement supplémentaire, vous pouvez nous contacter par téléphone au 03 89 40 07 97." . '<br>' .
                        "Nous vous remercions de votre compréhension et restons à votre disposition." . '</p>' .
                        '<p>' . "Cordialement," . ' <br>' . 
                        "L'équipe de 2JC Automobiles" . '</p>';   
                        
                // envoi du mail
                $mail->sendMail('Concernant votre demande', $body, 'Concernant votre demande'); // ajouter mail client 

                // on envois un message de confirmation et on redirige  
                $this->addFlash('success', 'Votre message a bien été envoyé.');
                return $this->redirectToRoute('app_admin_devis');

            } else {    
                throw $this->createNotFoundException('Action inconnue');
            }

            // envoi du mail
            $mail->sendMail('Concernant votre demande', $body, 'Concernant votre demande'); // ajouter mail client 

            // on envois un message de confirmation et on redirige  
            $this->addFlash('success', 'Votre message a bien été envoyé.');
            return $this->redirectToRoute('app_admin_rdv');
        }
}