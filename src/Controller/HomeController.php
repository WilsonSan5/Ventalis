<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProduitRepository $produitRepository, CategorieRepository $categorieRepository, JWTTokenManagerInterface $jwtManager): Response
    {
        // $token = $this->jwtManager->create($this->getUser());
        // dump($token);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'produits' => $produitRepository->findAll(),
            'categories' => $categorieRepository->findAll()
        ]);
    }
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }
}