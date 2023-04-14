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
    public function index(): Response
    {
        $user = $this->getUser();

        $messageries = $user->getMessageries(); //tableau d'objet messagerie
        
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'messageries' => $messageries,
            'user' => $user
        ]);
    }

    #[Route('/message/{id}', name: 'app_message_show', methods: ['GET'])]
    public function show(Messagerie $messagerie): Response
    {
        $messages = $messagerie->getMessages();
        
        return $this->render('message/show.html.twig', [
            'messagerie' => $messagerie,
            'messages' => $messages
        ]);
    }

    #[Route('/message/{id}/send', name: 'app_message_send', methods: ['POST'])]
    public function send(Messagerie $messagerie, MessageRepository $messageRepository, MessagerieRepository $messagerieRepository): Response
    {
        $messages = $messagerie->getMessages();

        if($_POST['message']){
            $newMessage = new Message;
            $newMessage->setContenu($_POST['message']);
            $newMessage->setAuthor($this->getUser());
            $newMessage->setMessagerie($messagerie);

            $messageRepository->save($newMessage,true);
        }

        return $this->render('message/show.html.twig', [
            'messagerie' => $messagerie,
            'messages' => $messages
        ]);
    }
}
