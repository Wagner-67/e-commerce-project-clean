<?php

namespace App\Entity;

use App\Repository\UserTokensRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTokensRepository::class)]
#[ApiResource]
class UserTokens
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userToken', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verifyToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $verifyTokenAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        if ($user->getUserToken() !== $this) {
            $user->setUserToken($this);
        }
        return $this;
    }

    public function getVerifyToken(): ?string
    {
        return $this->verifyToken;
    }

    public function setVerifyToken(?string $verifyToken): static
    {
        $this->verifyToken = $verifyToken;
        return $this;
    }

    public function getVerifyTokenAt(): ?\DateTimeImmutable
    {
        return $this->verifyTokenAt;
    }

    public function setVerifyTokenAt(?\DateTimeImmutable $verifyTokenAt): static
    {
        $this->verifyTokenAt = $verifyTokenAt;
        return $this;
    }
}
