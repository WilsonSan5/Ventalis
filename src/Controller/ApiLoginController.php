<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiLoginController extends AbstractController
{
    private $encoder;
    private $jwtManager;

    public function __construct(UserPasswordHasherInterface $encoder, JWTTokenManagerInterface $jwtManager)
    {
        $this->encoder = $encoder;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/login', name: 'app_api_login')]
    public function login(Request $request): Response
    {
        $token = $this->jwtManager->create($this->getUser());
        dump($token);
        $data = json_decode($request->getContent(), true);
        $username = $data['email'];
        $password = $data['password'];

        // Rechercher l'utilisateur en fonction du nom d'utilisateur (ou de tout autre critère de recherche)
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $username]);

        if (!$user || !$this->encoder->isPasswordValid($user, $password)) {
            return new Response('Invalid credentials', Response::HTTP_UNAUTHORIZED);
        }

        // Générer un token JWT
        $token = $this->jwtManager->create($user);

        // Retourner le token JWT en réponse
        return new Response(json_encode(['token' => $token]), Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}