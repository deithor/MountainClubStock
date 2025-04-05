<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'borrower', targetEntity: RentalRecord::class)]
    private Collection $rentalRecords;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: BasketItem::class)]
    private Collection $basket;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $middleName = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $balance = 0;

    public function __construct()
    {
        $this->basket = new ArrayCollection();
        $this->rentalRecords = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = UserRole::USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, BasketItem>
     */
    public function getBasket(): Collection
    {
        return $this->basket;
    }

    public function addBasketItem(BasketItem $basketItem): static
    {
        if (!$this->basket->contains($basketItem)) {
            $this->basket->add($basketItem);
            $basketItem->setUser($this);
        }

        return $this;
    }

    public function removeBasketItem(BasketItem $basketItem): static
    {
        if ($this->basket->removeElement($basketItem)) {
            // set the owning side to null (unless already changed)
            if ($basketItem->getUser() === $this) {
                $basketItem->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RentalRecord>
     */
    public function getRentalRecords(): Collection
    {
        return $this->rentalRecords;
    }

    public function addRentalRecord(RentalRecord $rentalRecord): static
    {
        if (!$this->basket->contains($rentalRecord)) {
            $this->basket->add($rentalRecord);
            $rentalRecord->setBorrower($this);
        }

        return $this;
    }

    public function removeRentalRecord(RentalRecord $rentalRecord): static
    {
        if ($this->rentalRecords->removeElement($rentalRecord)) {
            // set the owning side to null (unless already changed)
            if ($rentalRecord->getBorrower() === $this) {
                $rentalRecord->setBorrower(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): static
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): static
    {
        $this->balance = $balance;

        return $this;
    }
}
