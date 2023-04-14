<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

use App\Entity\User;
use App\Entity\Message;
use App\Entity\Messagerie;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MessageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {   
        $users = $manager->getRepository(User::class)->findBy(['matricule' => null]);
        $emps = $manager->getRepository(User::class)->findUserByRole('ROLE_EMP');

        for ($o = 0 ; $o< 10 ; $o ++){
            $messagerie = new Messagerie;
            $messagerie->setObjet('Objet : '.$o);
            $messagerie->addUser($users[mt_rand(0,count($users)-1)]);
            $messagerie->addUser($emps[mt_rand(0,count($emps)-1)]);
            for($m = 0 ; $m < 5 ; $m++){
                $message = new Message;
                $message->setContenu('Bonjour M.'.$m);
                $message->setMessagerie($messagerie);
                $message->setAuthor($messagerie->getUser()[mt_rand(0,1)]);
                $manager->persist($message);
            }
            $manager->persist($messagerie);
        };
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ProductFixtures::class,
        ];
    }
}