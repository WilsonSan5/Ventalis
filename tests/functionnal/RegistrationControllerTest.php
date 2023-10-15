<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;

class RegistrationControllerTest extends WebTestCase
{
    public function testSuccessfulRegistration()
    {
        $client = static::createClient();
        // Load the registration page
        $crawler = $client->request('GET', '/register');

        // Donnée en entrée
        $form = $crawler->selectButton('Je crée un compte')->form([
            'registration_form[email]' => 'randomUser@test.com',
            'registration_form[nom]' => 'Test',
            'registration_form[prenom]' => 'Test',
            'registration_form[plainPassword]' => 'UserMdp5!',
            'registration_form[agreeTerms]' => 1
        ]);

        $client->submit($form);

        // Supression du nouvel utilisateur
        $doctrine = $client->getContainer()->get('doctrine');
        $userRepository = $doctrine->getRepository(User::class);
        $newUser = $userRepository->findBy(['email' => 'randomUser@test.com']);
        $userRepository->remove($newUser[0], true);

        // S'il n'y a pas de d'alerte alors le test est valide
        $this->assertSelectorNotExists('.alert');
    }
    public function testWeakPassword()
    {
        $client = static::createClient();

        // Load the registration page
        $crawler = $client->request('GET', '/register');

        // Fill out the registration form with test user data
        $form = $crawler->selectButton('Je crée un compte')->form([
            'registration_form[email]' => 'randomUser@test.com',
            'registration_form[nom]' => 'Test',
            'registration_form[prenom]' => 'Test',
            'registration_form[plainPassword]' => 'usermdp',
            'registration_form[agreeTerms]' => 1
        ]);

        // Submit the form
        $client->submit($form);
        // Il doit y avoir une alerte
        $this->assertSelectorExists('.alert');
    }
    public function testAlreadyUsedEmail()
    {
        $client = static::createClient();
        // Ajout du nouvel utilisateur
        $doctrine = $client->getContainer()->get('doctrine');
        $userRepository = $doctrine->getRepository(User::class);
        $newUser = new User;
        $newUser->setEmail('alreadyUsedEmail@test.com')->setPassword('UserMdp5!');
        $userRepository->save($newUser, true);

        // Load the registration page
        $crawler = $client->request('GET', '/register');
        // Fill out the registration form with test user data
        $form = $crawler->selectButton('Je crée un compte')->form([
            'registration_form[email]' => 'alreadyUsedEmail@test.com',
            // Ajout d'un mail déjà existant
            'registration_form[nom]' => 'Test',
            'registration_form[prenom]' => 'Test',
            'registration_form[plainPassword]' => 'Usermdp5!',
            'registration_form[agreeTerms]' => 1
        ]);
        // Submit the form
        $client->submit($form);
        // Il doit y avoir une alerte


        // Supression du nouvel utilisateur
        $doctrine = $client->getContainer()->get('doctrine');
        $userRepository = $doctrine->getRepository(User::class);
        $newUser = $userRepository->findBy(['email' => 'alreadyUsedEmail@test.com']);
        $userRepository->remove($newUser[0], true);


        // Il doit y avoir une alerte
        $this->assertSelectorExists('.alert');
    }
}