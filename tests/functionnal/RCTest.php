<?php

namespace App\Tests;

use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RCTest extends WebTestCase
{
    public function testSomething()
    {
        //Créer un client HTTP (obligatoire dans un test fonctionnel)
        $client = static::createClient();

        // Récupérer le conteneur de service
        $container = $client->getContainer();

        // Récupérer le service Doctrine
        $doctrine = $container->get('doctrine');

        //Récupérer le repository de l'entité Users
        $userRepository = $doctrine->getRepository(User::class);

        //Récupérer les utilisateurs de ma table users
        $users = $userRepository->findAll();

        //Tester qu'il y au moins un utilisateur 
        $this->assertNotEmpty($users);
    }
}