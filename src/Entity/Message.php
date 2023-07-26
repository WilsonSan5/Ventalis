<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read', 'user_id:read']],
    denormalizationContext: ['groups' => ['write']],
)]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(SearchFilter::class, strategy: 'exact')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[Groups(['write'])]
    private ?Messagerie $Messagerie = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read', 'write'])]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[Groups(['write'])]
    private ?User $author = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_read = null;

    #[ORM\ManyToOne(inversedBy: 'message_received')]
    private ?user $recipient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessagerie(): ?Messagerie
    {
        return $this->Messagerie;
    }

    public function setMessagerie(?Messagerie $Messagerie): self
    {
        $this->Messagerie = $Messagerie;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function isIsRead(): ?bool
    {
        return $this->is_read;
    }

    public function setIsRead(?bool $is_read): self
    {
        $this->is_read = $is_read;

        return $this;
    }

    public function getRecipient(): ?user
    {
        return $this->recipient;
    }

    public function setRecipient(?user $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }
}