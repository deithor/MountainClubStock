<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RentalRecordRepository;
use App\Validator\ItemQuantityIsAvailableToGive;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RentalRecordRepository::class)]
#[ItemQuantityIsAvailableToGive]
class RentalRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rentalRecords')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Item $item = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?User $lender = null;

    #[ORM\ManyToOne(inversedBy: 'rentalRecords')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?User $borrower = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantity = 1;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $borrowedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $returnedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getLender(): ?User
    {
        return $this->lender;
    }

    public function setLender(?User $lender): static
    {
        $this->lender = $lender;

        return $this;
    }

    public function getBorrower(): ?User
    {
        return $this->borrower;
    }

    public function setBorrower(?User $borrower): static
    {
        $this->borrower = $borrower;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getBorrowedAt(): ?\DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(\DateTimeImmutable $borrowedAt): static
    {
        $this->borrowedAt = $borrowedAt;

        return $this;
    }

    public function getReturnedAt(): ?\DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function setReturnedAt(?\DateTimeImmutable $returnedAt): static
    {
        $this->returnedAt = $returnedAt;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
