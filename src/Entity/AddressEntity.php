<?php

namespace App\Entity;

use App\Repository\AddressEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressEntityRepository::class)]
#[ApiResource]
class AddressEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'address')]
    private ?user $user = null;

    #[Assert\NotBlank(message: "County is required.")]
    #[ORM\Column(length: 50)]
    private ?string $Country = null;

    #[Assert\NotBlank(message: "City is required.")]
    #[ORM\Column(length: 90)]
    private ?string $City = null;

    #[Assert\NotBlank(message: "Postal Code is required.")]
    #[ORM\Column(length: 90)]
    private ?string $PostalCode = null;

    #[Assert\NotBlank(message: "Street Address is required.")]
    #[ORM\Column(length: 90)]
    private ?string $StreetName = null;

    #[Assert\NotBlank(message: "Firstname is required.")]
    #[ORM\Column(length: 90)]
    private ?string $Firstname = null;

    #[Assert\NotBlank(message: "Lastname is required.")]
    #[ORM\Column(length: 255)]
    private ?string $Lastname = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'billingAddress', cascade: ['persist', 'remove'])]
    private ?Order $orders = null;

    #[ORM\OneToOne(mappedBy: 'shippingAddress', cascade: ['persist', 'remove'])]
    private ?Order $orders2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->Country;
    }

    public function setCountry(string $Country): static
    {
        $this->Country = $Country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->City;
    }

    public function setCity(string $City): static
    {
        $this->City = $City;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->PostalCode;
    }

    public function setPostalCode(string $PostalCode): static
    {
        $this->PostalCode = $PostalCode;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->StreetName;
    }

    public function setStreetName(string $StreetName): static
    {
        $this->StreetName = $StreetName;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->Firstname;
    }

    public function setFirstname(string $Firstname): static
    {
        $this->Firstname = $Firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->Lastname;
    }

    public function setLastname(string $Lastname): static
    {
        $this->Lastname = $Lastname;

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

    public function getOrders(): ?Order
    {
        return $this->orders;
    }

    public function setOrders(?Order $orders): static
    {
        // unset the owning side of the relation if necessary
        if ($orders === null && $this->orders !== null) {
            $this->orders->setBillingAddress(null);
        }

        // set the owning side of the relation if necessary
        if ($orders !== null && $orders->getBillingAddress() !== $this) {
            $orders->setBillingAddress($this);
        }

        $this->orders = $orders;

        return $this;
    }

    public function getOrders2(): ?Order
    {
        return $this->orders2;
    }

    public function setOrders2(?Order $orders2): static
    {
        // unset the owning side of the relation if necessary
        if ($orders2 === null && $this->orders2 !== null) {
            $this->orders2->setShippingAddress(null);
        }

        // set the owning side of the relation if necessary
        if ($orders2 !== null && $orders2->getShippingAddress() !== $this) {
            $orders2->setShippingAddress($this);
        }

        $this->orders2 = $orders2;

        return $this;
    }
}
