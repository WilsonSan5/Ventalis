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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository, ValidatorInterface $validator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $user->setRoles(['ROLE_USER']);
        $errors = $validator->validate($user);
        // Attribution du conseiller ---------------------------------------------------------
        $allUsers = $userRepository->findBy(['matricule' => null]);
        $allConseillers = $userRepository->findUserByRole('ROLE_EMP'); // récupère dans un tableau tous les employés
        // Calcul du nombre d'utilisateur lié à chaque employé
        $nbrOfUsers = 0; // C'est la valeur qu'on va stocker pour comparer à chaque boucle
        foreach ($allConseillers as $conseiller) { // On va boucler sur tout les conseillers
            $result = 0;
            foreach ($allUsers as $user2) { // On boucle sur tous les users, si son conseiller est celui de la boucle alors on compte +1
                if ($user2->getConseiller() == $conseiller) {
                    $result++;
                }
            } // Une fois qu'on a le nombre d'utilisteur (result) on compare avec nbrOfUser.
            if ($result < $nbrOfUsers || $nbrOfUsers == 0) { // Si le résultat est plus petit, on le garde, sinon on passe au suivant.
                $nbrOfUsers = $result;
                $user->setConseiller($conseiller); // Si le résultat est plus petit, alors on récupère l'id du conseiller est on le stocke
            }
        }
        //  FIN Attribution du conseiller -------------------------------------------------------
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

            return $this->redirectToRoute('app_login');
        }
        ;
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'errors' => $errors
        ]);
    }
}