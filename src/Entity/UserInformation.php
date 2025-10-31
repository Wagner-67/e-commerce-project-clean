<?php

namespace App\Entity;

use App\Repository\UserInformationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserInformationRepository::class)]
#[ApiResource]
class UserInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Firstname is required.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[Assert\NotBlank(message: "Lastname is required.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[Assert\NotBlank(message: "Phone Number is required.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[Assert\NotBlank(message: "Street Address is required.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetAddress = null;

    #[Assert\NotBlank(message: "City is required.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[Assert\NotBlank(message: "Postal Code is required.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $postCode = null;

    #[Assert\NotBlank(message: "County is required.")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\OneToOne(inversedBy: 'userInformation', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(?string $streetAddress): static
    {
        $this->streetAddress = $streetAddress;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getPostCode(): ?string
    {
        return $this->postCode;
    }

    public function setPostCode(?string $postCode): static
    {
        $this->postCode = $postCode;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        if ($user->getUserInformation() !== $this) {
            $user->setUserInformation($this);
        }
        return $this;
    }
}
