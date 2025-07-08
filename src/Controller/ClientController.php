<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\Devis;
use App\Form\RdvTypeForm;
use App\Form\DevisTypeForm;
use App\Service\ApiService;
use App\Form\ContactTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use PHPMailer\PHPMailer\PHPMailer;
use App\Service\MailService;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

#[Route('/client')]
final class ClientController extends AbstractController
{

// gestion api -----------------------------------------------------
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

// gestion des devis -----------------------------------------------------

    #[Route('/devis', name: 'app_devis_client')]
    public function devis(ApiService $apiService, Request $request, EntityManagerInterface $entityManager, MailService $mail): Response
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
            $devisData = $devisForm->getData();

            // on créer automatiquement un statut 
            $devisData->setStatut('En cours');
        
            // on envois vers la bdd
            $entityManager->persist($devisData);
            $entityManager->flush();
           
            // on prepare les mails vers l'administrateur et le client    
            $devisUrl = $this->generateUrl('show_devis', ['id' => $devisData->getId()], 0);             // On génère l'URL du devis
            $tel = $devisData->getTel() ? $devisData->getTel() : 'Non renseigné';                 // On gère la reception des champs non obligatoires
            $adminBody = '<strong>Vous avez reçu une nouvelle demande de devis.</strong> <br>' .
                            'Nom : ' . $devisData->getNom() . ' ' . $devisData->getPrenom() . '<br>' .
                            'Email : ' . $devisData->getEmail() . '<br>' .
                            'Téléphone : ' . $tel . '<br>' .
                            'Date de la demande : ' . $devisData->getDateDevis()->format('d-m-Y à H:m') . '<br>' .
                            '<a href="' . $devisUrl . '">Cliquer ici pour acceder au devis</a>';

            $clientBody = 'Votre demande de devis  n°' . $devisData->getId() . ' a bien été prise en compte.<br>' .
                            'Nous reviendrons vers vous très rapidement.'; 
            
            $mail->sendMail('Confirmation de votre demande de devis', $clientBody, 'Confirmation de votre demande de devis'); // ajouter $devisData->getEmail(), 
            $mail->sendMail('Nouvelle demande de devis', $adminBody, 'Nouvelle demande de devis.'); // ajouter mail admin
            
            // puis on redirige vers la liste des véhicules d'occasion
            $this->addFlash('success', 'Votre demande de devis a bien été prise en compte, nous reviendrons vers vous très rapidement.');
            return $this->redirectToRoute('app_devis_client');

            } elseif ($devisForm->isSubmitted() && !$devisForm->isValid()) {
                $this->addFlash('error', 'Erreur lors de la création de votre devis');
            }
                     
        return $this->render('client/devis.html.twig', [
            'controller_name' => 'ClientController',
            'marques' => $marquesResponse,
            'devisForm' => $devisForm->createView(),     
        ]);
    }

// gestion des rendez-vous -----------------------------------------------------

    #[Route('/rdv', name: 'app_rdv_client')]
    public function rdv(ApiService $apiService, Request $request, EntityManagerInterface $entityManager, MailService $mail): Response
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
            $rdvData = $rdvForm->getData();

            // on créer automatiquement un statut et un rappel
            $rdvData->setRappelRdv(0); // 0 = pas de rappe
            $rdvData->setStatut('En attente');

            // on envois vers la bdd
            $entityManager->persist($rdv);
            $entityManager->flush();
        
            // on prepare les mails vers l'administrateur et le client
                $rdvUrl = $this->generateUrl('show_rdv', ['id' => $rdvData->getId()], 0);             // On génère l'URL du devis
                $tel = $rdvData->getTel() ? $rdvData->getTel() : 'Non renseigné';                 // On gère la reception des champs non obligatoires
                $adminBody = '<strong>Vous avez reçu une nouvelle demande de rdv.</strong> <br>' .
                                'Nom : ' . $rdvData->getNom() . ' ' . $rdvData->getPrenom() . '<br>' .
                                'Email : ' . $rdvData->getEmail() . '<br>' .
                                'Téléphone : ' . $tel . '<br>' .
                                'Prestation : ' . $rdvData->getPrestation()->getNomPrestation() . '<br>' .
                                'Date du rdv : ' . $rdvData->getDateRdv()->format('d-m-Y à H:m') . '<br>' .
                                '<a href="' . $rdvUrl . '">Cliquer ici pour acceder à la demande de rdv</a>';
                $clientBody = 'Votre demande de rendez-vous n°' . $rdvData->getId() . ' a bien été prise en compte.<br>' .
                                'Nous reviendrons vers vous très rapidement. Veuillez attendre notre confirmation.';

                $mail->sendMail('Nouvelle demande de rdv', $adminBody, 'Nouvelle demande de rdv.'); // ajouter mail admin
                $mail->sendMail('Demande de rendez-vous', $clientBody, 'Votre demande de rendez-vous à bien été prise en compte.'); // ajouter mail client

                // puis on redirige vers la liste des véhicules d'occasion
                $this->addFlash('success', 'Votre demande de rendez-vous a bien été prise en compte, nous reviendrons vers vous très rapidement.');
                return $this->redirectToRoute('app_rdv_client');
                
            } elseif ($rdvForm->isSubmitted() && !$rdvForm->isValid()) {
                $this->addFlash('error', 'Erreur lors de la création de votre demande de rendez-vous');
            }       
    
        return $this->render('client/rdv.html.twig', [
            'controller_name' => 'ClientController',
            'marques' => $marquesResponse,
            'rdvForm' => $rdvForm->createView(),     
        ]);
    }

// gestion de la page de contact -----------------------------------------------------

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailService $mail): Response
    {
        // on crée un forumlaire pour la prise de contact
        $contactForm = $this->createForm(ContactTypeForm::class);

        // on gère la requête
        $contactForm->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
                    
        // on gère la reception des champs non obligatoires 
        if (!$contactForm->get('tel')->getData()) {
            $tel = 'Non renseigné';
        }
        else {
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
        $mail->sendMail('Nouvelle demande de contact', $body, 'Nouvelle demande de contact');

            // on envois un message de confirmation et on redirige  
            $this->addFlash('success', 'Votre message a bien été envoyé, nous reviendrons vers vous très rapidement.');
        } elseif ($contactForm->isSubmitted() && !$contactForm->isValid()) { 
            $this->addFlash('error', 'Votre demande de contact a echoué, veuillez réessayer plus tard.');
        }
        
        return $this->render('client/contact.html.twig', [
            'controller_name' => 'ClientController',
            'contactForm' => $contactForm->createView(),     
        ]);
    }
}
