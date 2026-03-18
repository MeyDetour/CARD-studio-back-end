<?php

namespace App\Entity;

use App\Repository\GameRepository;
use App\Service\ImageService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["games","game"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["games","game"])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["games","game"])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(["games","game"])]
    private ?int $playerCount = null;

    #[ORM\Column]
    #[Groups(["games","game"])]
    private ?int $gameCount = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["games","game"])]
    private ?string $types = null;

    #[ORM\Column]
    #[Groups(["game","games"])]
    private array $editionHistory = [];

    #[ORM\Column]
    #[Groups(["game"])]
    private array $globalValue = [];

    #[ORM\Column]
    #[Groups(["game"])]
    private array $playerGlobalValue = [];

    #[ORM\Column]
    #[Groups(["game","games"])]
    private array $params = [];

    #[ORM\Column]
    #[Groups(["game"])]
    private array $EventDemons = [];

    #[ORM\Column]
    #[Groups(["game"])]
    private array $EventEvents = [];

    #[ORM\Column]
    #[Groups(["game"])]
    private array $EventWithValueEvents = [];

    #[ORM\Column]
    #[Groups(["game"])]
    private array $assetsCard = [];

    #[ORM\Column]
    #[Groups(["game"])]
    private array $assetsGain = [];

    #[ORM\Column]
    #[Groups(["game"])]
    private array $roles = [];

    /**
     * @var Collection<int, Note>
     */
    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'Game', orphanRemoval: true)]
    #[Groups(["games","game"])]
    private Collection $notes;
 

    #[ORM\ManyToOne(inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["game"])]
    private ?User $creator = null;

    #[ORM\Column]
    #[Groups(["game"])]
    private ?bool $isPublic = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["game","games"])]
    private ?string $image = null;

 

    public function __construct()
    {
        $this->notes = new ArrayCollection(); 
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPlayerCount(): ?int
    {
        return $this->playerCount;
    }

    public function setPlayerCount(int $playerCount): static
    {
        $this->playerCount = $playerCount;

        return $this;
    }

    public function getGameCount(): ?int
    {
        return $this->gameCount;
    }

    public function setGameCount(int $gameCount): static
    {
        $this->gameCount = $gameCount;

        return $this;
    }

    public function getTypes(): ?string
    {
        return $this->types;
    }

    public function setTypes(string $types): static
    {
        $this->types = $types;

        return $this;
    }

    public function getEditionHistory(): array
    {
        return $this->editionHistory;
    }

    public function setEditionHistory(array $editionHistory): static
    {
        $this->editionHistory = $editionHistory;

        return $this;
    }

    public function getglobalValue(): array
    {
        return $this->globalValue;
    }

    public function setglobalValue(array $globalValue): static
    {
        $this->globalValue = $globalValue;

        return $this;
    }

    public function getPlayerGlobalValue(): array
    {
        return $this->playerGlobalValue;
    }

    public function setPlayerGlobalValue(array $playerGlobalValue): static
    {
        $this->playerGlobalValue = $playerGlobalValue;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $arams): static
    {
        $this->params = $arams;

        return $this;
    }

    public function getEventDemons(): array
    {
        return $this->EventDemons;
    }

    public function setEventDemons(array $EventDemons): static
    {
        $this->EventDemons = $EventDemons;

        return $this;
    }

    public function getEventEvents(): array
    {
        return $this->EventEvents;
    }

    public function setEventEvents(array $EventEvents): static
    {
        $this->EventEvents = $EventEvents;

        return $this;
    }

    public function getEventWithValueEvents(): array
    {
        return $this->EventWithValueEvents;
    }

    public function setEventWithValueEvents(array $EventWithValueEvents): static
    {
        $this->EventWithValueEvents = $EventWithValueEvents;

        return $this;
    }

    public function getAssetsCard(): array
    {
        return $this->assetsCard;
    }

    public function setAssetsCard(array $assetsCard): static
    {
        $this->assetsCard = $assetsCard;

        return $this;
    }

    public function getAssetsGain(): array
    {
        return $this->assetsGain;
    }

    public function setAssetsGain(array $assetsGain): static
    {
        $this->assetsGain = $assetsGain;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setGame($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getGame() === $this) {
                $note->setGame(null);
            }
        }

        return $this;
    }
 
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;
        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

 
}
