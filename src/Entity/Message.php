<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sendBy = null;

    #[ORM\ManyToOne(inversedBy: 'messagesReceived')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sendTo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $sendingDate = null;

    #[ORM\Column]
    private ?bool $isRead = false;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'ReplyTo')]
    private ?self $replyTo = null;

    #[ORM\OneToMany(mappedBy: 'replyTo', targetEntity: self::class)]
    private Collection $ReplyTo;

    public function __construct()
    {
        $this->ReplyTo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSendBy(): ?User
    {
        return $this->sendBy;
    }

    public function setSendBy(?User $sendBy): self
    {
        $this->sendBy = $sendBy;

        return $this;
    }

    public function getSendTo(): ?User
    {
        return $this->sendTo;
    }

    public function setSendTo(?User $sendTo): self
    {
        $this->sendTo = $sendTo;

        return $this;
    }

    public function getSendingDate(): ?\DateTimeInterface
    {
        return $this->sendingDate;
    }

    public function setSendingDate(\DateTimeInterface $sendingDate): self
    {
        $this->sendingDate = $sendingDate;

        return $this;
    }

    public function isIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getReplyTo(): ?self
    {
        return $this->replyTo;
    }

    public function setReplyTo(?self $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function addReplyTo(self $replyTo): self
    {
        if (!$this->ReplyTo->contains($replyTo)) {
            $this->ReplyTo->add($replyTo);
            $replyTo->setReplyTo($this);
        }

        return $this;
    }

    public function removeReplyTo(self $replyTo): self
    {
        if ($this->ReplyTo->removeElement($replyTo)) {
            // set the owning side to null (unless already changed)
            if ($replyTo->getReplyTo() === $this) {
                $replyTo->setReplyTo(null);
            }
        }

        return $this;
    }
}
