<?php

namespace App\Entity;

use App\Repository\UserNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserNotificationRepository::class)]
class UserNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column]
    private ?bool $isRead = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    //Usuario que recibe la notificación
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    //Usuario que envía la notificación 
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $initiator = null;

    //Para que cada notificación esté asociada con una solicitud
    #[ORM\ManyToOne(targetEntity: UserFollow::class)]
    private ?UserFollow $relatedFollow = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getInitiator(): ?User
    {
        return $this->initiator;
    }
    
    public function setInitiator(?User $initiator): static
    {
        $this->initiator = $initiator;
        return $this;
    }

    public function getRelatedFollow(): ?UserFollow
    {
        return $this->relatedFollow;
    }

    public function setRelatedFollow(?UserFollow $relatedFollow): static
    {
        $this->relatedFollow = $relatedFollow;
        return $this;
    }

}
