<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use App\Form\ContactType;


class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mail = (new TemplatedEmail())
                ->from($form->getData()['email'])
                ->to('wilsonsan.dev@gmail.com')
                ->subject($form->getData()['objet'])
                ->htmlTemplate('contact/message-template.html.twig')
                ->context([
                    'user_email' => $form->getData()['email'],
                    'objet' => $form->getData()['objet'],
                    'message' => $form->getData()['message']
                ]);

            $this->addFlash('success', 'Votre message a été envoyé avec succès.'); // permet de créer un message flash pour la session en cours
            // 1er paramètre : la variable que le twig va lire avec app.flash(param1,message)
            // enfin le contenu du flash.
            $mailer->send($mail);

            return $this->redirectToRoute('app_contact', [
                'sent' => true
            ]);
        }
        ;

        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
            'form' => $form,
            'sent' => false
        ]);
    }
}