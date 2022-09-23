<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Character::class)]
    private Collection $characters;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Summary::class, orphanRemoval: true)]
    private Collection $summaries;

    public function __construct()
    {
        $this->characters = new ArrayCollection();
        $this->summaries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    /**
     * @return Collection<int, Character>
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function addCharacter(Character $character): self
    {
        if (!$this->characters->contains($character)) {
            $this->characters->add($character);
            $character->setGame($this);
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        if ($this->characters->removeElement($character)) {
            // set the owning side to null (unless already changed)
            if ($character->getGame() === $this) {
                $character->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Summary>
     */
    public function getSummaries(): Collection
    {
        return $this->summaries;
    }

    public function addSummary(Summary $summary): self
    {
        if (!$this->summaries->contains($summary)) {
            $this->summaries->add($summary);
            $summary->setGame($this);
        }

        return $this;
    }

    public function removeSummary(Summary $summary): self
    {
        if ($this->summaries->removeElement($summary)) {
            // set the owning side to null (unless already changed)
            if ($summary->getGame() === $this) {
                $summary->setGame(null);
            }
        }

        return $this;
    }
}
