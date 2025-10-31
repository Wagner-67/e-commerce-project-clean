<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use App\Enum\PaymentMethod;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ApiResource]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    private ?User $user = null;

    #[ORM\Column(enumType: PaymentMethod::class)]
    private ?PaymentMethod $type = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $providerPaymentId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(length: 100)]
    private string $provider;

    #[ORM\Column(type: 'boolean')]
    private bool $isDefault = false;

    #[ORM\OneToOne(mappedBy: 'paymentMethod', cascade: ['persist', 'remove'])]
    private ?Order $orders = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getType(): ?PaymentMethod
    {
        return $this->type;
    }

    public function setType(?PaymentMethod $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getProviderPaymentId(): string
    {
        return $this->providerPaymentId;
    }

    public function setProviderPaymentId(string $providerPaymentId): static
    {
        $this->providerPaymentId = $providerPaymentId;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function getOrders(): ?Order
    {
        return $this->orders;
    }

    public function setOrders(?Order $orders): static
    {
        // unset the owning side of the relation if necessary
        if ($orders === null && $this->orders !== null) {
            $this->orders->setPaymentMethod(null);
        }

        // set the owning side of the relation if necessary
        if ($orders !== null && $orders->getPaymentMethod() !== $this) {
            $orders->setPaymentMethod($this);
        }

        $this->orders = $orders;

        return $this;
    }
}
