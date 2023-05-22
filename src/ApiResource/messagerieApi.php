<?php

namespace App\Controller\Api;

use ApiPlatform\Core\Resource\Resource;
use App\Entity\Messagerie;
use App\Entity\Message;
use App\Repository\MessagerieRepository;
use App\Repository\MessageRepository;

class MessagerieRessource extends Resource
{
    public function get(MessageRepository $messageRepository)
    {
        // Retrieve the product from the database.
        return $messageRepository->findAll();
        // Return the product.
    }
}