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
        } else
            $produits = $produitRepository->findAll();

        return $this->render('programmes/index.html.twig', [
            'produits' => $produits,
            // la méthode findAll() renvoie le repository en tableau.
            'form' => $form,
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
        $achat->setPrix($planning->getPrix()* $quantite);

        $achat->setDateAchat($date);
        $achat->setStatus('inCart');

        $achatRepository->save($achat, true);

        return $this->redirectToRoute('app_compte_panier', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/programmes/{id}/payment', name: 'app_programmes_payment', methods: ['GET'])]
    public function buy(Produit $produit, PlanningRepository $planningRepository): Response
    {
        $id_planning = $_GET['planning'];
        $quantite = $_GET['quantite'];
        $planning = $planningRepository->findOneBy(['id' => $id_planning]);

        return $this->render('programmes/payment.html.twig', [
            'produit' => $produit,
            'planning' => $planning,
            'quantite' => $quantite
        ]);
    }

    #[Route('/intentPayment', name: 'app_paiement_stripe')]
    public function intentStripe(SerializerInterface $serializerInterface): JsonResponse
    {
        dump('intentpayment');
        //Insérer la clé secrète pour relier votre clé public à la clé secret
        Stripe::setApiKey('sk_test_51Mf1j1FufBPCUONNJMWBzxMnyfHa5NdSycSU0Tclj0zPTktHfwIPaaEP4R3SwfBCgtpuE6o4aIpsPgu0F1vMOH6y00kbKWYWQF');

        header('Content-type : application/json');

        try {

            $jsonStr = file_get_contents('php://input');
            $jsonObj = json_decode($jsonStr);

            dump($jsonObj);

            //Créer l'intention de paiment avec le prix et le device
            $paymentIntent = PaymentIntent::create([
                'amount' => $jsonObj->items[0]->prix * 100,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => 'Paiement de ' . $jsonObj->items[0]->prenom . ' ' . $jsonObj->items[0]->nom
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];

            return $this->json([
                'clientSecret' => $output['clientSecret']
            ]);


        } catch (Error $e) {
            http_response_code(500);
            echo json_decode(['error' => $e->getMessage()]);
        }

        return $this->json([], Response::HTTP_NOT_FOUND);
    }

    #[Route('/confirmation', name: 'app_programmes_confirmation')]
    public function confirm(ProduitRepository $produitRepository, AchatRepository $achatRepository, UserInterface $userinterface, PlanningRepository $planningRepository): Response
    {
        $date = new DateTime();
        $date->format('d/m/Y H:m');

        $donnees = $_GET['donnees'];
        $jsonData = json_decode($donnees);
        $id_produit = $jsonData[0]->id_produit;
        $id_planning = $jsonData[0]->id_planning;

        $produit = $produitRepository->findOneBy(['id' => $id_produit]);
        $planning = $planningRepository->findOneBy(['id' => $id_planning]);
        $prix = $planning->getPrix();
        $achat = new Achat;

        $achat->setUser($userinterface);
        $achat->setProduit($produit);
        $achat->setPlanning($planning);
        $achat->setPrix($prix);

        $achat->setDateAchat($date);
        $achat->setQuantite(1);
        $achat->setStatus(true);

        $achatRepository->save($achat, true);

        return $this->render('programmes/confirmation.html.twig', [
            // 'produit' => $produit,
            'donnees' => $jsonData,
            'produit' => $produit,
            'planning' => $planning,

        ]);
    }
}