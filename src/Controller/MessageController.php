<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Messagerie;
use App\Entity\Message;
use App\Repository\MessagerieRepository;
use App\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Request;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(MessageRepository $messageRepository, MessagerieRepository $messagerieRepository): Response
    {
        $user = $this->getUser();
        if ($this->getUser()->getRoles()[0] == "ROLE_EMP") {

            return $this->redirectToRoute('app_home');
        }

        $messageries = $user->getMessageries(); //tableau d'objet messagerie

        if (isset($_GET['filter'])) {
            $filter = $_GET['filter'];
        } else
            $filter = 'all';


        return $this->render('message/index.html.twig', [
            'messageries' => $messageries,
            'user' => $user,
            'filter' => $filter
        ]);
    }

    #[Route('/message/new', name: 'app_message_new', methods: ['POST'])]
    public function new(MessagerieRepository $messagerieRepository, MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        $messageries = $user->getMessageries();

        if ($_POST['objet'] && $_POST['message']) {
            $newObjet = $_POST['objet'];
            $newContenu = $_POST['message'];

            $newMessage = new Message;
            $newMessagerie = new Messagerie;

            $newMessagerie->setObjet($newObjet);
            $newMessagerie->addUser($this->getUser());
            $newMessagerie->addUser($this->getUser()->getConseiller());

            $newMessage->setAuthor($this->getUser());
            $newMessage->setContenu($newContenu);
            $newMessage->setMessagerie($newMessagerie);

            $messagerieRepository->save($newMessagerie, true);
            $messageRepository->save($newMessage, true);

            return $this->redirectToRoute('app_message_show', ['id' => $newMessagerie->getId()]);
        }
        return $this->render('message/index.html.twig', [
            'messageries' => $messageries,
            'user' => $user
        ]);
    }

    #[Route('/message/{id}', name: 'app_message_show', methods: ['GET'])]
    public function show(Messagerie $messagerie): Response
    {
        $user = $this->getUser();
        $messageries = $user->getMessageries();
        $messages = $messagerie->getMessages();

        if (isset($_GET['filter'])) {
            $filter = $_GET['filter'];
        } else
            $filter = 'all';

        return $this->render('message/show.html.twig', [
            'messageries' => $messageries,
            'currentMessageries' => $messagerie,
            'messages' => $messages,
            'user' => $user,
            'filter' => $filter
        ]);
    }

    #[Route('/message/{id}/send', name: 'app_message_send', methods: ['POST'])]
    public function send(Messagerie $messagerie, MessageRepository $messageRepository, MessagerieRepository $messagerieRepository): Response
    {
        $messages = $messagerie->getMessages();

        if ($_POST['message']) {
            $newMessage = new Message;
            $newMessage->setContenu($_POST['message']);
            $newMessage->setAuthor($this->getUser());
            $newMessage->setMessagerie($messagerie);

            $messageRepository->save($newMessage, true);

            return $this->redirectToRoute('app_message_show', ['id' => $messagerie->getId()]);
        }

        return $this->render('message/show.html.twig', [
            'messagerie' => $messagerie,
            'messages' => $messages
        ]);
    }

    #[Route('/api/messageries/new', methods: ['POST'])]

    public function createMessagerie(Request $request, MessagerieRepository $messagerieRepository)
    {
        // Obtenez les données du corps de la requête
        $data = json_decode($request->getContent(), true);

        // Créez une nouvelle entité Messagerie
        $messagerie = new Messagerie();

        // Persistez la messagerie dans le gestionnaire d'entités
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($messagerie);
        $entityManager->flush();

        // Obtenez les utilisateurs associés à la messagerie à partir des données de la requête
        $userIds = $data['users']; // Remplacez 'users' par le nom du champ qui contient les ID des utilisateurs

        // Ajoutez les utilisateurs associés à la messagerie
        foreach ($userIds as $userId) {
            $user = $entityManager->getRepository(User::class)->find($userId);
            if ($user) {
                $messagerie->addUser($user);
            }
        }

        // Persistez à nouveau la messagerie pour enregistrer les relations avec les utilisateurs
        $entityManager->persist($messagerie);
        $entityManager->flush();

        // Retournez une réponse avec les données de la messagerie nouvellement créée si nécessaire
        return $this->json($messagerie);
    }
}