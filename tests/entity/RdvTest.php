<?php
namespace App\Tests\Entity;

use App\Entity\Rdv;
use PHPUnit\Framework\TestCase;

class RdvTest extends TestCase
{
    public function testRdvCreation()
    {
        $rdv = new Rdv();
        $rdv->setEmail('client@test.fr');
        $rdv->setNom('NOM');
        $rdv->setPrenom('Prenom');
        $rdv->setTel('000000');
        $rdv->setStatut('Test');
        $rdv->setDateRdv(new \DateTimeImmutable('2023-10-01T10:00:00'));

        $prestation = new \App\Entity\Prestation();
        $rdv->setPrestation($prestation);
        $vehicule = new \App\Entity\Vehicule();
        $rdv->setVehicule($vehicule);
        
        $this->assertEquals('client@test.fr', $rdv->getEmail());
        $this->assertEquals('NOM', $rdv->getNom()); 
        $this->assertEquals('Prenom', $rdv->getPrenom()); 
        $this->assertEquals('000000', $rdv->getTel()); 
        $this->assertEquals('Test', $rdv->getStatut()); 
        $this->assertEquals(new \DateTimeImmutable('2023-10-01T10:00:00'), $rdv->getDateRdv()); 
        $this->assertEquals($prestation, $rdv->getPrestation()); 
        $this->assertEquals($vehicule, $rdv->getVehicule()); 
    }
    
    public function testValidEmailAreAccepted()
    {
        $rdv = new rdv();
        
        //  Test emails VALIDES
        $validEmails = [
            'test@garage2jc.fr',
            'client@gmail.com',
            'user.name@domain.co.uk',
            'info@garage-2jc.com'
        ];
        
        foreach ($validEmails as $email) {
            $rdv->setEmail($email);
            $this->assertStringContainsString('@', $rdv->getEmail());
            $this->assertStringContainsString('.', $rdv->getEmail());
            $this->assertTrue(filter_var($rdv->getEmail(), FILTER_VALIDATE_EMAIL) !== false);
        }

    }

    public function testInvalidEmailAreRejected()
    {
        $rdv = new Rdv();

        //  Test emails INVALIDES
        $invalidEmails = [
            'email-sans-arobase',
            '@domain.com',
            'user@',
            '',
            'user@domain',
            'user space@domain.com'
        ];
        
        foreach ($invalidEmails as $email) {
            $rdv->setEmail($email);
            $this->assertFalse(filter_var($rdv->getEmail(), FILTER_VALIDATE_EMAIL));
        }
    }
}