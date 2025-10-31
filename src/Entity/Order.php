<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToOne(inversedBy: 'orders', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?AddressEntity $billingAddress = null;

    #[ORM\OneToOne(inversedBy: 'orders2', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?AddressEntity $shippingAddress = null;

    #[ORM\OneToOne(inversedBy: 'orders', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Payment $paymentMethod = null;

    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Cart $cart = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $totalAmount = 0;

    #[ORM\Column(length: 50)]
    private string $status = 'pending';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getBillingAddress(): ?AddressEntity
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?AddressEntity $billingAddress): static
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function getShippingAddress(): ?AddressEntity
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?AddressEntity $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function getPaymentMethod(): ?Payment
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?Payment $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): static
    {
        $this->cart = $cart;
        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
