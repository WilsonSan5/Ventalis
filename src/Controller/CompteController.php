<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Repository\AchatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Form\CompteEditType;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

// Importation des bundles de paiement stripe
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/compte')]
class CompteController extends AbstractController
{

    // Page d'accueil de compte (Elle va contenir 2 boutons : Achats et Commane)

    #[Route('/', name: 'app_compte')]
    public function index(): Response
    {

        return $this->render('compte/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/achat', name: 'app_compte_achat')]
    public function achat(): Response
    {
        return $this->render('compte/achat.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/panier', name: 'app_compte_panier')]
    public function panier(AchatRepository $achatRepository): Response
    {

        $produitsInCart = $achatRepository->findBy(['user' => $this->getUser(), 'status' => 'inCart']);
        $totalPanier = 0;

        foreach ($produitsInCart as $produit) {
            $totalPanier = $totalPanier + $produit->getPrix();
        }

        return $this->render('compte/panier.html.twig', [
            'user' => $this->getUser(),
            'produitsInCart' => $produitsInCart,
            'totalPanier' => $totalPanier
        ]);
    }

    #[Route('/panier/paiement', name: 'app_panier_payment', methods: ['GET'])]
    public function buy(AchatRepository $achatRepository): Response
    {
        $totalPanier = 0;
        $produitsInCart = $achatRepository->findBy(['user' => $this->getUser(), 'status' => 'inCart']); // je vais chercher tout les produits lié à l'user et qui ont l'attribut 'inCart'
        if (count($produitsInCart) == 0) {
            return $this->redirectToRoute('app_compte_panier');
        } else {
            foreach ($produitsInCart as $produit) {
                $totalPanier = $totalPanier + $produit->getPrix();
            }
        }
        return $this->render('compte/payment.html.twig', [
            'user' => $this->getUser(),
            'produitsInCart' => $produitsInCart,
            'totalPanier' => $totalPanier
        ]);
    }

    #[Route('/utilisateur/edit', name: 'app_compte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserInterface $userInterface, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(CompteEditType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $userInterface->setRoles(['ROLE_USER']);

            $userRepository->save($user, true);
            return $this->redirectToRoute('app_compte', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('compte/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}