<?php

namespace App\Controller;


use App\Form\CategorieFilterType;
use App\Repository\CategorieRepository;
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
    public function index(ProduitRepository $produitRepository, Request $request, CategorieRepository $categorieRepository): Response
    {
        $form = $this->createForm(CategorieFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { //Si L'utilisateur filtre avec le formulaire de la page programme
            if ($form->getData()['categorie'] == 'Tous') {
                $produits = $produitRepository->findAll(); // la méthode findAll() renvoie le repository en tableau d'objets.
                $is_filtered = false;
                $filter = $form->getData()['categorie'];
            } else {
                $filter = $form->getData()['categorie']; // champs categorie du form
                $produits = $produitRepository->findByCategorie($filter); // méthode créee dans le repository par chatgpt...
                $is_filtered = true;
            }
        } else if (isset($_GET['categorie_id'])) { // Si l'utilisateur filtre depuis la homepage. On récupère l'id de la categorie en GET
            $filter = $categorieRepository->findOneBy(['id' => $_GET['categorie_id']]);
            $produits = $produitRepository->findByCategorie($filter);
            $is_filtered = true;
        } else {
            $produits = $produitRepository->findAll(); // la méthode findAll() renvoie le repository en tableau d'objets.
            $is_filtered = false;
            $filter = $categorieRepository->findOneBy(['id' => 1]);
        }
        return $this->render('programmes/index.html.twig', [
            'produits' => $produits,
            'form' => $form,
            'is_filtered' => $is_filtered,
            'filter' => $filter
        ]);
    }

    #[Route('/programmes/{id}', name: 'app_programmes_show', methods: ['GET'])]
    public function show(Produit $produit, Request $request, ProduitRepository $produitRepository): Response
    {
        return $this->render('programmes/show.html.twig', [
            'produit' => $produit,
            'addedInCart' => false
        ]);
    }

    #[Route('/programmes/{id}/addToCart', name: 'app_programmes_addCart', methods: ['GET'])]
    public function addToCart(Produit $produit, PlanningRepository $planningRepository, ProduitRepository $produitRepository, AchatRepository $achatRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED'); // Sil'utilisateur n'est pas connecté, il est renvoyé au app_login

        $id_planning = $_GET['planning'];
        $quantite = $_GET['quantite'];
        $planning = $planningRepository->findOneBy(['id' => $id_planning]);

        $userCart = $achatRepository->findBy(['user' => $this->getUser(), 'status' => 'inCart']); // récupération de tous les plannings dans le panier

        foreach ($userCart as $achatInCart) { // Pour chaque planning on va vérifier si il est identique au planning aujouté par l'utilisateur
            $planningInCart = $achatInCart->getPlanning();
            if ($planningInCart->getId() == $id_planning) {
                $achatInCart->setQuantite($achatInCart->getQuantite() + $quantite); // S'il est identique : on ajoute à la quantité la nouvelle quantité choisie.
                $achatInCart->setPrix($planning->getPrix() * $achatInCart->getQuantite());

                $achatRepository->save($achatInCart, true); // On sauvegarde
                $this->addFlash('success', 'La quantité a été mise à jour');
                return $this->redirectToRoute('app_programmes_show', [
                    'id' => $produit->getId(),
                    'user' => $this->getUser(),
                    'addedInCart' => true // variable qui va permettre l'affichage d'un popup
                ]);
            }
        }

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
        $this->addFlash('success', 'Le voyage a bien été ajouté au panier');
        return $this->redirectToRoute('app_programmes_show', [
            'id' => $produit->getId(),
            'user' => $this->getUser(),
            'addedInCart' => true // variable qui va permettre l'affichage d'un popup
        ]);
    }
}