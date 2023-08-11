<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\AccessToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\AccessTokenRepository;

class ApiLoginController extends AbstractController
{
    private $encoder;
    private $jwtManager;
    private $accessTokenRepository;

    public function __construct(UserPasswordHasherInterface $encoder, JWTTokenManagerInterface $jwtManager, AccessTokenRepository $accessTokenRepository)
    {
        $this->encoder = $encoder;
        $this->jwtManager = $jwtManager;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    #[Route('/api/login', name: 'app_api_login')]
    public function login(Request $request, UserRepository $userRepository): Response
    {
        $token = $this->jwtManager->create($this->getUser());
        dump($token);
        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $password = $data['security']['credentials']['password'];

        // Rechercher l'utilisateur en fonction du nom d'utilisateur (ou de tout autre critère de recherche)
        $user = $userRepository->findOneBy(['email' => $username]);

        if (!$user || !$this->encoder->isPasswordValid($user, $password)) {
            return new Response('Invalid credentials', Response::HTTP_UNAUTHORIZED);
        }

        // Générer un token JWT
        $token = $this->jwtManager->create($user);

        // Stocker le token dans le repository pour sécuriser les tokens
        // $accessToken = new AccessToken;
        // $accessToken->setToken($token);
        // $this->accessTokenRepository->save($accessToken, true);

        // Retourner le token JWT en réponse
        return new Response(json_encode(['token' => $token]), Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}