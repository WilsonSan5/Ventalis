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


class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(MessageRepository $messageRepository, MessagerieRepository $messagerieRepository): Response
    {
        $user = $this->getUser();
        $messageries = $user->getMessageries(); //tableau d'objet messagerie

        return $this->render('message/index.html.twig', [
            'messageries' => $messageries,
            'user' => $user
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

        return $this->render('message/show.html.twig', [
            'allMessageries' => $messageries,
            'currentMessageries' => $messagerie,
            'messages' => $messages,
            'user' => $user
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

}