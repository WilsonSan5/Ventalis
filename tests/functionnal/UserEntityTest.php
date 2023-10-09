<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;

class UserEntityTest extends KernelTestCase
{
    public function testValidEntity()
    {
        $container = static::getContainer();
        $user = new User;
        $user->setEmail('UserEntityTest@gmail.com');
        $user->setPassword('TestPassword1!');
        $user->setRoles(['ROLE_USER']);
        $user->setNom('Test');
        $user->setPrenom('Test');

        $errors = $container->get('validator')->validate($user);
        $this->assertCount(0, $errors);
    }

    public function testUnvalidEntity()
    {
        $container = static::getContainer();
        $user = new User;
        $user->setEmail('testuser@gmail.com'); // Email déjà utilisée
        $user->setPassword('TestPassword1!');
        $user->setRoles(['ROLE_USER']);
        $user->setNom('Test');
        $user->setPrenom('Test');

        $errors = $container->get('validator')->validate($user);
        $this->assertCount(1, $errors);
    }
}