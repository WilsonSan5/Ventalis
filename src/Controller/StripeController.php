<?php

namespace App\Controller;

use App\Repository\PlanningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AchatRepository;

use DateTime;

use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;


class StripeController extends AbstractController
{
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
    public function confirm(AchatRepository $achatRepository, PlanningRepository $planningRepository): Response
    {
        $date = new DateTime();
        $date->format('d/m/Y H:m');

        $inCart = $achatRepository->findBy(['user' => $this->getUser(), 'status' => 'inCart']);

        foreach ($inCart as $achat) {
            // Je change l'état de 'inCart' -> 'bought' pour le supprimer du panier et les afficher dans achats
            $achat->setStatus('bought');
            $achat->setDateAchat($date);

            // réduction du nombre de places restantes en fonction de la quantité acheté par l'user.
            $planningQuantite = $achat->getPlanning()->getQuantite();
            $planningQuantite = $planningQuantite - $achat->getQuantite();
            $planning = $achat->getPlanning();
            $planning->setQuantite($planningQuantite);


            $planningRepository->save($planning, true);
            $achatRepository->save($achat, true);
        }
        return $this->render('programmes/confirmation.html.twig', [
            // 'produit' => $produit,

        ]);
    }

}