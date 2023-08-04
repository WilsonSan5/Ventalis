<?php

namespace App\Entity;

use ApiPlatform\Metadata\GetCollection;
use App\Repository\AchatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    operations: [
        new Get(normalizationContext: ['groups' => ['singleAchat:read']]),
        new GetCollection(normalizationContext: ['groups' => ['achats:read']])
    ]
)]
class Achat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['singleAchat:read', 'achats:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'achats')]
    #[Groups(['singleAchat:read', 'achats:read'])]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'achats')]
    #[Groups(['singleAchat:read', 'achats:read'])]

    private ?Planning $planning = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['singleAchat:read', 'achats:read'])]
    private ?int $prix = null;

    #[ORM\ManyToOne(inversedBy: 'achat')]
    #[Groups(['singleAchat:read', 'achats:read'])]

    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['singleAchat:read', 'achats:read'])]
    private ?\DateTimeInterface $dateAchat = null;

    #[ORM\Column]
    #[Groups(['singleAchat:read', 'achats:read'])]
    private ?int $quantite = null;


    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): self
    {
        $this->planning = $planning;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(?int $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->dateAchat;
    }

    public function setDateAchat(\DateTimeInterface $dateAchat): self
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}