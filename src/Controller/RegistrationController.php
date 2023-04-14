<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use Faker;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {   

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $user->setRoles(['ROLE_USER']);

        $allUsers = $userRepository->findBy(['matricule' => null]);
        $allConseiller = $userRepository->findUserByRole('ROLE_EMP'); // récupère dans un tableau tous les employés

        $nbrOfUsers = 0;
        
        foreach($allConseiller as $key => $conseiller){
            $result = 0;
            
            foreach($allUsers as $key => $user2){

                if( $user2->getConseiller()  == $conseiller){
                    $result++;
                }
            }
            if($result < $nbrOfUsers || $nbrOfUsers == 0 ){
                $nbrOfUsers = $result;
                $conseiller_id = $conseiller->getId();
            }
        }
        $conseiller = $userRepository->findOneBy(['id' => $conseiller_id]);

        $user->setConseiller($conseiller); // Attribue un id aléatoire entre 0 et le nombre d'employés
        dump($user->getConseiller());


        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
        
            return $this->redirectToRoute('app_home');
        }
        

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
