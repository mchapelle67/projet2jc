<?php
namespace App\Tests\Entity;

use App\Entity\Devis;
use PHPUnit\Framework\TestCase;

class DevisTest extends TestCase
{
    public function testRdvSettersAndGettersReturnCorrectValues()
    {
        $devis = new Devis();
        $devis->setEmail('client@test.fr');
        $devis->setText('Salut, je passe juste faire un test.');
        $devis->setNom('NOM');
        $devis->setPrenom('Prenom');
        $devis->setTel('000000');
        $devis->setStatut('Test');

        $prestation = new \App\Entity\Prestation();
        $devis->setPrestation($prestation);
        $vehicule = new \App\Entity\Vehicule();
        $devis->setVehicule($vehicule);
        
        $this->assertEquals('client@test.fr', $devis->getEmail());
        $this->assertEquals('Salut, je passe juste faire un test.', $devis->getText());
        $this->assertEquals('NOM', $devis->getNom()); 
        $this->assertEquals('Prenom', $devis->getPrenom()); 
        $this->assertEquals('000000', $devis->getTel()); 
        $this->assertEquals('Test', $devis->getStatut()); 
        $this->assertEquals($prestation, $devis->getPrestation()); 
        $this->assertEquals($vehicule, $devis->getVehicule()); 
    }
    
    public function testValidEmailsAreAccepted()
    {
        $devis = new Devis();
        
        // Test emails VALIDES
        $validEmails = [
            'test@garage2jc.fr',
            'client@gmail.com',
            'user.name@domain.co.uk',
            'info@garage-2jc.com'
        ];
        
        foreach ($validEmails as $email) {
            $devis->setEmail($email);
            $this->assertStringContainsString('@', $devis->getEmail());
            $this->assertStringContainsString('.', $devis->getEmail());
            $this->assertTrue(filter_var($devis->getEmail(), FILTER_VALIDATE_EMAIL) !== false);
        }
    }

    public function testInvalidEmailsAreRejected()
    {
        $devis = new Devis();

        // Test emails INVALIDES
        $invalidEmails = [
            'email-sans-arobase',
            '@domain.com',
            'user@',
            '',
            'user@domain',
            'user space@domain.com'
        ];
        
        foreach ($invalidEmails as $email) {
            $devis->setEmail($email);
            $this->assertFalse(filter_var($devis->getEmail(), FILTER_VALIDATE_EMAIL));
        }
    }
}