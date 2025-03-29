<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $quantity = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $price = null;

    #[ORM\Column(length: 511, nullable: true)]
    #[Assert\Length(max: 511)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: RentalRecord::class)]
    private Collection $rentalRecords;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Category $category = null;

    public function __construct()
    {
        $this->rentalRecords = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
        if (!$this->rentalRecords->contains($rentalRecord)) {
            $this->rentalRecords->add($rentalRecord);
            $rentalRecord->setItem($this);
        }

        return $this;
    }

    public function removeRentalRecord(RentalRecord $rentalRecord): static
    {
        if ($this->rentalRecords->removeElement($rentalRecord)) {
            // set the owning side to null (unless already changed)
            if ($rentalRecord->getItem() === $this) {
                $rentalRecord->setItem(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
