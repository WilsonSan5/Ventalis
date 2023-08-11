<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class CheckTokenController extends AbstractController
{
    #[Route('/api/checkToken', name: 'app_api_check_token', methods: ['POST'])]
    public function validateToken(Request $request, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        try {
            $token = $request->headers->get('Authorization');

            if (!$token) {
                return new JsonResponse(['message' => 'Token manquant'], 401);
            }

            // Validez le token en utilisant le service JWTTokenManager
            $jwtManager->decode($token);

            return new JsonResponse(['message' => 'Token valide']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Token invalide'], 401);
        }
    }
}