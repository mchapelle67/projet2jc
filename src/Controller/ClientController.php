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
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

    #[Route('/contact/client', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
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

        // on récupère les données du formulaire et on les prépare à l'envoi
        $contactData = $contactForm->getData();
        $mail = new PHPMailer(true);

        try {
            // paramètre du serveur SMTP
            $mail->SMTPDebug = 2;                                   // affiche les messages de debug (mettre à 0 en prod)
            $mail->Debugoutput = 'error_log';                         // pour que ça aille dans les logs PHP
            $mail->isSMTP();                                            // Simple Mail Transfer Protocol
            $mail->Host       = 'smtp.gmail.com';                     // configuration du serveur SMTP
            $mail->SMTPAuth   = true;                                   // active l'authentification SMTP
            $mail->Username   = 'manon.chp68@gmail.com';                     //SMTP username
            $mail->Password   = 'cihmdotrnhdlkgva';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // sert à crypter la connexion
            $mail->Port       = 465;                                    // port du serveur SMTP

            // réglages de l'expéditeur et du destinataire
            $mail->setFrom('manon.chp68@gmail.com', '2jc');
            $mail->addAddress('manon.chp68@gmail.com');     

            // contenu du message
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Nouvelle demande de contact';
            $mail->Body    = 'Vous avez reçu un nouveau message : <br>' .
                            'Nom : ' . $contactData['nom'] . ' ' . $contactData['prenom'] . '<br>' .
                            'Email : ' . $contactData['email'] . '<br>' .
                            'Teléphone : ' . $tel . '<br>' .
                            'Message : ' . nl2br($contactData['text']);
            $mail->AltBody = 'Ceci est le corps du message en texte brut pour les clients mail ne supportant pas le HTML';        

            // envoi du mail
            $mail->send();

            // on envois un message de confirmation et on redirige  
            $this->addFlash('success', 'Votre message a bien été envoyé, nous reviendrons vers vous très rapidement.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de votre message : ' . $e->getMessage());
            }
        
        return $this->render('client/contact.html.twig', [
            'controller_name' => 'ClientController',
            'contactForm' => $contactForm->createView(),     
        ]);
        }
    }
}
