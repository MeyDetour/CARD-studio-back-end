<?php

namespace App\Entity;

use App\Repository\DeckRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeckRepository::class)]
class Deck
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['deck'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['deck'])]   
    private ?string $authorName = null;

    #[ORM\Column]
    #[Groups(['deck'])]
    private ?bool $isPublished = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['deck'])]
    private ?array $cards = null;

    #[ORM\Column]
    #[Groups(['deck'])] 
    private array $params = [];

    #[ORM\ManyToOne(inversedBy: 'decks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['deck'])]
    private ?User $owner = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['deck'])]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): static
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getCards(): ?array
    {
        return $this->cards;
    }

    public function setCards(?array $cards): static
    {
        $this->cards = $cards;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
