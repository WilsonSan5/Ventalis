<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\PropertyFilter;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    operations: [
        new Get(),
        new GetCollection()
    ]
)]
#[ApiFilter(PropertyFilter::class)]

#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'messagerie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $prenom = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Achat::class)]

    private Collection $achat;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomEntreprise = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $conseiller = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $matricule = null;

    #[ORM\ManyToMany(targetEntity: Messagerie::class, mappedBy: 'User')]
    #[Groups(['user:read'])]

    private Collection $messageries;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Message::class)]

    private Collection $messages;

    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: Message::class)]
    private Collection $message_received;





    public function __construct()
    {
        $this->achat = new ArrayCollection();
        $this->messageries = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->message_received = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return Collection<int, Achat>
     */
    public function getAchat(): Collection
    {
        return $this->achat;
    }

    public function addAchat(Achat $achat): self
    {
        if (!$this->achat->contains($achat)) {
            $this->achat->add($achat);
            $achat->setUser($this);
        }

        return $this;
    }

    public function removeAchat(Achat $achat): self
    {
        if ($this->achat->removeElement($achat)) {
            // set the owning side to null (unless already changed)
            if ($achat->getUser() === $this) {
                $achat->setUser(null);
            }
        }

        return $this;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(?string $nomEntreprise): self
    {
        $this->nomEntreprise = $nomEntreprise;

        return $this;
    }

    public function getConseiller(): ?self
    {
        return $this->conseiller;
    }

    public function setConseiller(?self $conseiller): self
    {
        $this->conseiller = $conseiller;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    /**
     * @return Collection<int, Messagerie>
     */
    public function getMessageries(): Collection
    {
        return $this->messageries;
    }


    public function addMessagery(Messagerie $messagery): self
    {
        if (!$this->messageries->contains($messagery)) {
            $this->messageries->add($messagery);
            $messagery->addUser($this);
        }

        return $this;
    }

    public function removeMessagery(Messagerie $messagery): self
    {
        if ($this->messageries->removeElement($messagery)) {
            $messagery->removeUser($this);
        }

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
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessageReceived(): Collection
    {
        return $this->message_received;
    }

    public function addMessageReceived(Message $messageReceived): self
    {
        if (!$this->message_received->contains($messageReceived)) {
            $this->message_received->add($messageReceived);
            $messageReceived->setRecipient($this);
        }

        return $this;
    }

    public function removeMessageReceived(Message $messageReceived): self
    {
        if ($this->message_received->removeElement($messageReceived)) {
            // set the owning side to null (unless already changed)
            if ($messageReceived->getRecipient() === $this) {
                $messageReceived->setRecipient(null);
            }
        }

        return $this;
    }
}