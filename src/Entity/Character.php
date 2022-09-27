<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: '`character`')]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $lastName;

    #[ORM\Column(length: 255)]
    private string $firstName;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(12)]
    #[Assert\LessThanOrEqual(80)]
    private int $age;

    #[ORM\Column(length: 255)]
    private string $disease;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $story = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $powers = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    private int $money = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bag = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(20)]
    private int $stamina;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(20)]
    private int $strength;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(20)]
    private int $agility;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(20)]
    private int $speed;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(20)]
    private int $charisma;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(20)]
    private int $intelligence;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(20)]
    private int $resilience;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(20)]
    private int $luck;

    #[ORM\ManyToOne(inversedBy: 'characters')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'characters')]
    private ?Game $game = null;

    #[ORM\Column]
    private bool $isPublic = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getDisease(): ?string
    {
        return $this->disease;
    }

    public function setDisease(string $disease): self
    {
        $this->disease = $disease;

        return $this;
    }

    public function getStory(): ?string
    {
        return $this->story;
    }

    public function setStory(?string $story): self
    {
        $this->story = $story;

        return $this;
    }

    public function getPowers(): ?string
    {
        return $this->powers;
    }

    public function setPowers(?string $powers): self
    {
        $this->powers = $powers;

        return $this;
    }

    public function getMoney(): ?int
    {
        return $this->money;
    }

    public function setMoney(int $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getBag(): ?string
    {
        return $this->bag;
    }

    public function setBag(?string $bag): self
    {
        $this->bag = $bag;

        return $this;
    }

    public function getStamina(): ?int
    {
        return $this->stamina;
    }

    public function setStamina(int $stamina): self
    {
        $this->stamina = $stamina;

        return $this;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): self
    {
        $this->strength = $strength;

        return $this;
    }

    public function getAgility(): ?int
    {
        return $this->agility;
    }

    public function setAgility(int $agility): self
    {
        $this->agility = $agility;

        return $this;
    }

    public function getSpeed(): ?int
    {
        return $this->speed;
    }

    public function setSpeed(int $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getCharisma(): ?int
    {
        return $this->charisma;
    }

    public function setCharisma(int $charisma): self
    {
        $this->charisma = $charisma;

        return $this;
    }

    public function getIntelligence(): ?int
    {
        return $this->intelligence;
    }

    public function setIntelligence(int $intelligence): self
    {
        $this->intelligence = $intelligence;

        return $this;
    }

    public function getResilience(): ?int
    {
        return $this->resilience;
    }

    public function setResilience(int $resilience): self
    {
        $this->resilience = $resilience;

        return $this;
    }

    public function getLuck(): ?int
    {
        return $this->luck;
    }

    public function setLuck(int $luck): self
    {
        $this->luck = $luck;

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

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function isIsPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }
}
