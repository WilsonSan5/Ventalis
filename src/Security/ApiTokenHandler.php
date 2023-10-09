<?php
namespace App\Security;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use App\Repository\AccessTokenRepository;

class ApiTokenHandler implements AccessTokenHandlerInterface
{
    private $userRepository;
    private $jwtManager;
    private $accessTokenRepository;
    public function __construct(UserRepository $userRepository, JWTTokenManagerInterface $jwtManager, AccessTokenRepository $accessTokenRepository)
    {
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->accessTokenRepository = $accessTokenRepository;
    }
    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        try {
            $payload = $this->jwtManager->parse($accessToken);
        } catch (JWTDecodeFailureException $e) {
            throw new BadCredentialsException('Invalid credentials.');
        }
        // Récupération l'ID de l'utilisateur
        $userId = $payload['id'];
        // Recherche de l'utilisateur en utilisant le repository
        $user = $this->userRepository->findOneBy(['id' => $userId]);
        if (!$user) {
            throw new BadCredentialsException('Utilisateur introuvable');
        }
        $userRoles = $user->getRoles();
        if (!$userRoles[1]) {
            throw new BadCredentialsException('Accès interdit !');
        }
        return new UserBadge($user->getUserIdentifier());
    }
}