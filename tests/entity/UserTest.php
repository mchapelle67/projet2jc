<?php
namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $user = new User();
        $user->setEmail('test@garage2jc.fr');
        $user->setRoles(['ROLE_USER']);
        
        $this->assertEquals('test@garage2jc.fr', $user->getEmail());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles()); 
    }
    
    public function testUserIdentifier()
    {
        $user = new User();
        $user->setEmail('admin@garage2jc.fr');
        
        $this->assertEquals('admin@garage2jc.fr', $user->getUserIdentifier());
    }
}