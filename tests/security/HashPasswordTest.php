<?php 

namespace App\Tests;

use App\Entity\User;
use PHPUnit\Framework\TestCase; 

class HashPasswordTest extends TestCase
{
    public function testUserPasswordHashing()
    {
        $user = new User();
        $plainPassword = 'password123';
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
        
        $user->setPassword($hashedPassword);
        
        // Assert that the password is hashed
        $this->assertNotEquals($plainPassword, $user->getPassword());
        
        // Assert that the password can be verified
        $this->assertTrue(password_verify($plainPassword, $user->getPassword()));
    }
}