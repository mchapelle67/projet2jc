<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\Devis;
use App\Form\RdvTypeForm;
use App\Form\DevisTypeForm;
use App\Service\ApiService;
use App\Service\MailService;
use App\Form\ContactTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/client')]
final class ClientController extends AbstractController
{
// gestion api ---------------------------------------------------------------------
    // #[Route('/api/modeles', name: 'api_modeles')]
    // public function apiModeles(ApiService $apiService, Request $request): Response
    // {
    //     $marque = $request->query->get('marque');
    //     $modeles = [];
    //     if ($marque) {
    //         $modeles = $apiService->getModelsByMake($marque);
    //     }

    //     return $this->json($modeles);
    // }

// gestion des devis -----------------------------------------------------
    #[Route('/devis', name: 'app_devis_client')]
    public function devis(ApiService $apiService, Request $request, EntityManagerInterface $entityManager, MailService $mail, RateLimiterFactory $contactLimiter): Response
    {
        // on récupère toutes les marques de véhicules
        // $marquesResponse = $apiService->getAllMakes();

        // on crée un forumlaire pour le devis
        $devis = new Devis(); 
        $devisForm = $this->createForm(DevisTypeForm::class, $devis);

        // on gère la requête
        $devisForm->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($devisForm->isSubmitted() && $devisForm->isValid()) {

            // on vérifie le rate limiter
            $limiter = $contactLimiter->create(($request->getClientIp()));
                if (!$limiter->consume(1)->isAccepted()) {
                    $this->addFlash('error', 'Trop de formulaires envoyés. Veuillez patienter.');
                    return $this->redirectToRoute('app_home');
                }
                    
            // on récupère les données du formulaire
            $devisData = $devisForm->getData();

            // on créer automatiquement un statut 
            $devisData->setStatut('En cours');
        
            // on envois vers la bdd
            $entityManager->persist($devisData);
            $entityManager->flush();
           
            // on prepare les mails vers l'administrateur et le client    
            $devisUrl = $this->generateUrl('show_devis', [
                'slug' => $devisData->getSlug() // on génère l'URL du devis avec le slug
            ], 0); // On génère l'URL du devis avec un hash slug
            $tel = $devisData->getTel() ? $devisData->getTel() : 'Non renseigné';                 // On gère la reception des champs non obligatoires
            $mailClient = $devisData->getEmail();
            $adminBody = '<strong>Vous avez reçu une nouvelle demande de devis.</strong> <br>' .
                            'Nom : ' . $devisData->getNom() . ' ' . $devisData->getPrenom() . '<br>' .
                            'Email : ' . $devisData->getEmail() . '<br>' .
                            'Téléphone : ' . $tel . '<br>' .
                            'Date de la demande : ' . $devisData->getDateDevis()->format('d-m-Y à H:i') . '<br>' .
                            '<a href="' . $devisUrl . '">Cliquer ici pour acceder au devis</a>';

            $clientBody = 'Votre demande de devis a bien été prise en compte.<br>' .
                            'Nous reviendrons vers vous très rapidement.'; 
            
            $mail->sendMail('Confirmation de votre demande de devis', $clientBody, 'Confirmation de votre demande de devis', $mailClient); 
            $mail->sendMail('Nouvelle demande de devis', $adminBody, 'Nouvelle demande de devis.', '2jcautomobiles@gmail.com'); 
            
            // puis on redirige vers la liste des véhicules d'occasion
            $this->addFlash('success', 'Votre demande de devis a bien été prise en compte, nous reviendrons vers vous très rapidement.');
            return $this->redirectToRoute('app_home');

            } elseif ($devisForm->isSubmitted() && !$devisForm->isValid()) {
                $this->addFlash('error', 'Erreur lors de la création de votre devis');
            }
                     
        return $this->render('client/devis.html.twig', [
            'controller_name' => 'ClientController',
            // 'marques' => $marquesResponse,
            'devisForm' => $devisForm->createView(),     
        ]);
    }

// gestion des rendez-vous -----------------------------------------------------
    #[Route('/rdv', name: 'app_rdv_client')]
    public function rdv(ApiService $apiService, Request $request, EntityManagerInterface $entityManager, MailService $mail, RateLimiterFactory $contactLimiter): Response
    {
        // on récupère toutes les marques de véhicules
        // $marquesResponse = $apiService->getAllMakes();

        // on crée un forumlaire pour la prise de rdv
        $rdv = new Rdv(); 
        $rdvForm = $this->createForm(RdvTypeForm::class, $rdv);

        // on gère la requête
        $rdvForm->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($rdvForm->isSubmitted() && $rdvForm->isValid()) {

            // on vérifie le rate limiter
            $limiter = $contactLimiter->create(($request->getClientIp()));
                if (!$limiter->consume(1)->isAccepted()) {
                    $this->addFlash('error', 'Trop de formulaires envoyés. Veuillez patienter.');
                    return $this->redirectToRoute('app_home');
                }

            // on vérifie que la date du rendez-vous n'est pas un dimanche ou un lundi au cas où javascript n'est pas prit en compte par le navigateur 
            $date = $rdv->getDateRdv();
            if (in_array((int) $date->format('w'), [0, 1])) {
                $this->addFlash('error', 'Les rendez-vous ne sont pas possibles les dimanches et lundis.');
                return $this->redirectToRoute('app_rdv_client');
            }
 
                    
            // on récupère les données du formulaire
            $rdvData = $rdvForm->getData();

            // on créer automatiquement un statut et un rappel
            $rdvData->setRappelRdv(0); // 0 = pas de rappel
            $rdvData->setStatut('En attente');

            // on envois vers la bdd
            $entityManager->persist($rdv);
            $entityManager->flush();
        
            // on prepare les mails vers l'administrateur et le client
            $rdvUrl = $this->generateUrl('show_rdv', [
                    'slug' => $rdvData->getSlug()
                    ], 0); // On génère l'URL du rdv avec le slug uniquement
            $tel = $rdvData->getTel() ? $rdvData->getTel() : 'Non renseigné';                 // On gère la reception des champs non obligatoires
            $mailClient = $rdvData->getEmail();
            $adminBody = '<strong>Vous avez reçu une nouvelle demande de rdv.</strong> <br>' .
                        'Nom : ' . $rdvData->getNom() . ' ' . $rdvData->getPrenom() . '<br>' .
                        'Email : ' . $rdvData->getEmail() . '<br>' .
                        'Téléphone : ' . $tel . '<br>' .
                        'Date du rdv : ' . $rdvData->getDateRdv()->format('d-m-Y à H:i') . '<br>' .
                        'Prestation : ' . $rdvData->getPrestation()->getNomPrestation() . '<br>' .
                        '<a href="' . $rdvUrl . '">Cliquer ici pour acceder à la demande de rdv</a>';
            $clientBody = 'Votre demande de rendez-vous a bien été prise en compte.<br>' .
                        'Date du rdv : ' . $rdvData->getDateRdv()->format('d-m-Y à H:i') . '<br>' .
                        'Prestation : ' . $rdvData->getPrestation()->getNomPrestation() . '<br>' .
                        'Nous reviendrons vers vous très rapidement. ' . '<br> ' .
                        '<strong>' . 'Veuillez attendre notre confirmation.' . '</strong>';

            $mail->sendMail('Demande de rendez-vous', $clientBody, 'Votre demande de rendez-vous à bien été prise en compte.', $mailClient); 
            $mail->sendMail('Nouvelle demande de rdv', $adminBody, 'Nouvelle demande de rdv.', '2jcautomobiles@gmail.com'); 

            // puis on redirige vers la liste des véhicules d'occasion
            $this->addFlash('success', 'Votre demande de rendez-vous a bien été prise en compte, nous reviendrons vers vous très rapidement.');
            return $this->redirectToRoute('app_home');
                
            } elseif ($rdvForm->isSubmitted() && !$rdvForm->isValid()) {
                $this->addFlash('error', 'Erreur lors de la création de votre demande de rendez-vous');
                return $this->redirectToRoute('app_rdv_client');
            }       
    
        return $this->render('client/rdv.html.twig', [
            'controller_name' => 'ClientController',
            // 'marques' => $marquesResponse,
            'rdvForm' => $rdvForm->createView(),     
        ]);
    }
// gestion de la page de contact -----------------------------------------------------
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailService $mail, RateLimiterFactory $contactLimiter): Response
    {
        // on crée un forumlaire pour la prise de contact
        $contactForm = $this->createForm(ContactTypeForm::class);

        // on gère la requête
        $contactForm->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($contactForm->isSubmitted() && $contactForm->isValid()) {

            // on vérifie le rate limiter
            $limiter = $contactLimiter->create(($request->getClientIp()));
                if (!$limiter->consume(1)->isAccepted()) {
                    $this->addFlash('error', 'Trop de messages envoyés. Veuillez patienter.');
                    return $this->redirectToRoute('app_contact');
                }
                        
                // on gère la reception des champs non obligatoires 
                if (!$contactForm->get('tel')->getData()) {
                    $tel = 'Non renseigné';
                } else {
                    $tel = $contactForm->get('tel')->getData();
                }

        // on met des majuscules pour rendre plus esthetique
        $nomData = $contactForm->get('nom')->getData();
        $nom = strtoupper($nomData);
        $prenomData = $contactForm->get('prenom')->getData();
        $prenom = ucfirst(strtolower($prenomData));

        // on récupère les données du formulaire et on les prépare à l'envoi
        $contactData = $contactForm->getData();
        $body = '<strong>Vous avez reçu un nouveau message.</strong> <br>' .
                    'Nom : ' . $nom . ' ' . $prenom . '<br>' .
                    'Email : ' . $contactData['email'] . '<br>' .
                    'Teléphone : ' . $tel . '<br>' .
                    'Message : ' . nl2br($contactData['text']);

        // envoi du mail
        $mail->sendMail('Nouvelle demande de contact', $body, 'Nouvelle demande de contact', '2jcautomobiles@gmail.com');

        // on envois un message de confirmation et on redirige  
        $this->addFlash('success', 'Votre message a bien été envoyé, nous reviendrons vers vous très rapidement.');
        return $this->redirectToRoute('app_home');
            
        } elseif ($contactForm->isSubmitted() && !$contactForm->isValid()) { 
            $this->addFlash('error', 'Votre demande de contact a echoué, veuillez réessayer plus tard.');
        }
        
        return $this->render('client/contact.html.twig', [
            'controller_name' => 'ClientController',
            'contactForm' => $contactForm->createView() 
        ]);
    }

// vues services et présentation du garage ---------------------------------------------
    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('client/services.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }
}