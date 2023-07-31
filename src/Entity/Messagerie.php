<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\MessagerieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;


#[ApiResource(
    normalizationContext: ['groups' => ['messagerie:read']],
    denormalizationContext: ['groups' => ['messagerie:write']],
)]

#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(SearchFilter::class, strategy: 'exact')]


#[ORM\Entity(repositoryClass: MessagerieRepository::class)]
class Messagerie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'messagerie:read'])]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'messageries')]
    #[Groups(['messagerie:write'])]
    private Collection $User;

    #[ORM\Column(length: 255, nullable: true)]

    private ?string $objet = null;

    #[ORM\OneToMany(mappedBy: 'Messagerie', targetEntity: Message::class, cascade: ['persist'])]
    #[Groups(['messagerie:read'])]

    private Collection $messages;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    public function __construct()
    {
        $this->User = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->User;
    }


    public function addUser(User $user): self
    {
        if (!$this->User->contains($user)) {
            $this->User->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->User->removeElement($user);

        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): self
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setMessagerie($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getMessagerie() === $this) {
                $message->setMessagerie(null);
            }
        }

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }
}