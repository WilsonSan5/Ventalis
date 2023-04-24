<?php

namespace App\Controller;


use App\Form\CategorieFilterType;
use App\Repository\ProduitRepository;
use App\Repository\PlanningRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;

use App\Repository\AchatRepository;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\Achat;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

// Importation des bundles de paiement stripe
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;


class ProgrammesController extends AbstractController
{
    #[Route('/programmes', name: 'app_programmes', methods: ['GET', 'POST'])]
    public function index(ProduitRepository $produitRepository, Request $request): Response
    {
        $form = $this->createForm(CategorieFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filter = $form->getData()['categorie']; // champs categorie du form
            $produits = $produitRepository->findByCategorie($filter); // méthode crée dans le repository par chatgpt...
            $is_filtered = true;
        } else {
            $produits = $produitRepository->findAll();
            $is_filtered = false;
        }

        return $this->render('programmes/index.html.twig', [
            'produits' => $produits,
            // la méthode findAll() renvoie le repository en tableau d'objets.
            'form' => $form,
            'is_filtered' => $is_filtered
        ]);
    }

    #[Route('/programmes/{id}', name: 'app_programmes_show', methods: ['GET'])]
    public function show(Produit $produit, Request $request, ProduitRepository $produitRepository): Response
    {

        return $this->render('programmes/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/programmes/{id}/addToCart', name: 'app_programmes_addCart', methods: ['GET'])]
    public function addToCart(Produit $produit, PlanningRepository $planningRepository, ProduitRepository $produitRepository, AchatRepository $achatRepository): Response
    {
        $id_planning = $_GET['planning'];
        $quantite = $_GET['quantite'];
        $planning = $planningRepository->findOneBy(['id' => $id_planning]);

        $date = new DateTime();
        $date->format('d/m/Y H:m');

        $achat = new Achat;

        $achat->setUser($this->getUser());
        $achat->setProduit($produit);
        $achat->setPlanning($planning);
        $achat->setQuantite($quantite);
        $achat->setPrix($planning->getPrix() * $quantite);

        $achat->setDateAchat($date);
        $achat->setStatus('inCart');

        $achatRepository->save($achat, true);

        return $this->redirectToRoute('app_compte_panier', [
            'user' => $this->getUser(),
        ]);
    }

}