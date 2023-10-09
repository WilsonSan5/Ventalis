<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Doctrine\ORM\EntityManagerInterface;
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
            'registration_form[email]' => 'randomUser@example.com',
            'registration_form[nom]' => 'Test',
            'registration_form[prenom]' => 'Test',
            'registration_form[plainPassword]' => 'UserMdp5!',
            'registration_form[agreeTerms]' => 1
        ]);

        $client->submit($form);
        // S'il n'y a pas de d'alerte alors le test est valide
        $this->assertSelectorNotExists('.alert');

        // Supression du nouvel utilisateur
        $doctrine = $client->getContainer()->get('doctrine');
        $userRepository = $doctrine->getRepository(User::class);
        $newUser = $userRepository->findBy(['email' => 'randomUser@example.com']);
        $userRepository->remove($newUser[0], true);
    }
    public function testWeakPassword()
    {
        $client = static::createClient();

        // Load the registration page
        $crawler = $client->request('GET', '/register'); // Replace with the URL of your registration page

        // Fill out the registration form with test user data
        $form = $crawler->selectButton('Je crée un compte')->form([
            'registration_form[email]' => 'randomUser@example.com',
            'registration_form[nom]' => 'Test',
            'registration_form[prenom]' => 'Test',
            'registration_form[plainPassword]' => 'usermdp',
            'registration_form[agreeTerms]' => 1
            // Add any other form fields required for registration
        ]);

        // Submit the form
        $client->submit($form);
        // Il doit y avoir une alerte
        $this->assertSelectorExists('.alert');
    }
    public function testAlreadyUsedEmail()
    {
        $client = static::createClient();

        // Load the registration page
        $crawler = $client->request('GET', '/register'); // Replace with the URL of your registration page
        // Fill out the registration form with test user data
        $form = $crawler->selectButton('Je crée un compte')->form([
            'registration_form[email]' => 'user11@gmail.com',
            // Ajout d'un mail déjà existant
            'registration_form[nom]' => 'Test',
            'registration_form[prenom]' => 'Test',
            'registration_form[plainPassword]' => 'Usermdp5!',
            'registration_form[agreeTerms]' => 1
            // Add any other form fields required for registration
        ]);
        // Submit the form
        $client->submit($form);
        // Il doit y avoir une alerte
        $this->assertSelectorExists('.alert');
    }
}