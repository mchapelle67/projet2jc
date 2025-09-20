<?php

namespace App\Service;
 
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public function sendMail($subject, $body, $altBody, $email) 
    {
        // on prepare le mail vers l'administrateur
        $mail = new PHPMailer(true);
        
        // paramètre du serveur SMTP
        $mail->SMTPDebug = 2;                                   // affiche les messages de debug (mettre à 0 en prod)
        $mail->Debugoutput = 'error_log';                         // pour que ça aille dans les logs PHP
        $mail->isSMTP();                                            // Simple Mail Transfer Protocol
        $mail->Host       = 'smtp.gmail.com';                     // configuration du serveur SMTP
        $mail->SMTPAuth   = true;                                   // active l'authentification SMTP
        $mail->Username   = 'jerome.midas68@gmail.com';                     //SMTP username
        $mail->Password = $_SERVER['MAILER_PASSWORD'] ?? getenv('MAILER_PASSWORD');  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // sert à crypter la connexion
        $mail->Port       = 465;                                    // port du serveur SMTP

        // réglages de l'expéditeur et du destinataire
        $mail->setFrom('jerome.midas68@gmail.com', '2jc Automobiles'); // adresse de l'expéditeur
        $mail->addAddress($email);      // adresse du destinataire

        // contenu du message
        $mail->isHTML(true);                                  //Set email format to HTML    
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;        
        
        try {
            $mail->send();
            return true;
        } catch (\Exception $e) {
            return false;
        }    }
}
