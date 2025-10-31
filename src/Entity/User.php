<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column]
    private bool $verified = false;

    #[ORM\Column(length: 255)]
    private string $role = 'ROLE_USER';

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLogin = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $accCreated = null;

    #[ORM\OneToOne(targetEntity: UserTokens::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserTokens $userToken = null;

    #[ORM\OneToOne(targetEntity: UserInformation::class, inversedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserInformation $userInformation = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Cart $cart = null;

    /**
     * @var Collection<int, AddressEntity>
     */
    #[ORM\OneToMany(targetEntity: AddressEntity::class, mappedBy: 'user')]
    private Collection $address;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'user')]
    private Collection $payments;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    public function __construct()
    {
        $this->accCreated = new \DateTimeImmutable();
        $this->address = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;
        return $this;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function setRoles(array $roles): static
    {
        $this->role = $roles[0] ?? 'ROLE_USER';
        return $this;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }


    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void {}

    public function getLastLogin(): ?\DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeImmutable $lastLogin): static
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    public function getAccCreated(): ?\DateTimeImmutable
    {
        return $this->accCreated;
    }

    public function setAccCreated(?\DateTimeImmutable $accCreated): static
    {
        $this->accCreated = $accCreated;
        return $this;
    }

    public function getUserToken(): ?UserTokens
    {
        return $this->userToken;
    }

    public function setUserToken(UserTokens $userToken): static
    {
        if ($userToken->getUser() !== $this) {
            $userToken->setUser($this);
        }

        $this->userToken = $userToken;
        return $this;
    }

    public function getUserInformation(): ?UserInformation
    {
        return $this->userInformation;
    }

    public function setUserInformation(UserInformation $userInformation): static
    {
        if ($userInformation->getUser() !== $this) {
            $userInformation->setUser($this);
        }

        $this->userInformation = $userInformation;
        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): static
    {
        if ($cart === null && $this->cart !== null) {
            $this->cart->setUser(null);
        }

        if ($cart !== null && $cart->getUser() !== $this) {
            $cart->setUser($this);
        }

        $this->cart = $cart;

        return $this;
    }

    /**
     * @return Collection<int, AddressEntity>
     */
    public function getAddress(): Collection
    {
        return $this->address;
    }

    public function addAddress(AddressEntity $address): static
    {
        if (!$this->address->contains($address)) {
            $this->address->add($address);
            $address->setUser($this);
        }

        return $this;
    }

    public function removeAddress(AddressEntity $address): static
    {
        if ($this->address->removeElement($address)) {
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setUser($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getUser() === $this) {
                $payment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }
}
