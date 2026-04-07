<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use App\Entity\Manufacturer;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name is required.')]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'mfr', referencedColumnName: 'id')]
    #[Assert\NotNull(message: 'Manufacturer is required.')]
    private ?Manufacturer $mfr = null;

    #[ORM\ManyToOne(targetEntity: ProductType::class)]
    #[ORM\JoinColumn(name: 'type', referencedColumnName: 'id')]
    #[Assert\NotNull(message: 'Type is required.')]
    private ?ProductType $type = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $calories = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $protein = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $fat = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $sodium = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $fiber = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $carbo = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $sugars = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $potass = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $vitamins = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $shelf = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $weight = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $cups = null;

    // #[ORM\Column(nullable: true)]
    // private ?float $rating = null;

    /**
     * @var ArrayCollection<int, ProductRating>
     */
    #[ORM\OneToMany(targetEntity: ProductRating::class, mappedBy: 'product')]
    private ?Collection $product_ratings = null;

    /**
     * @var ArrayCollection<int, ProductImage>
     */
    #[Groups(['single_product_show'])]
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'product')]
    private ?Collection $product_images = null;

    public function __construct()
    {
        $this->product_ratings = new ArrayCollection();
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

    public function getMfr(): ?Manufacturer
    {
        return $this->mfr;
    }

    public function setMfr(Manufacturer $mfr): static
    {
        $this->mfr = $mfr;

        return $this;
    }

    public function getType(): ?ProductType
    {
        return $this->type;
    }

    public function setType(ProductType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(int $calories): static
    {
        $this->calories = $calories;

        return $this;
    }

    public function getProtein(): ?int
    {
        return $this->protein;
    }

    public function setProtein(int $protein): static
    {
        $this->protein = $protein;

        return $this;
    }

    public function getFat(): ?int
    {
        return $this->fat;
    }

    public function setFat(int $fat): static
    {
        $this->fat = $fat;

        return $this;
    }

    public function getSodium(): ?int
    {
        return $this->sodium;
    }

    public function setSodium(int $sodium): static
    {
        $this->sodium = $sodium;

        return $this;
    }

    public function getFiber(): ?float
    {
        return $this->fiber;
    }

    public function setFiber(float $fiber): static
    {
        $this->fiber = $fiber;

        return $this;
    }

    public function getCarbo(): ?float
    {
        return $this->carbo;
    }

    public function setCarbo(float $carbo): static
    {
        $this->carbo = $carbo;

        return $this;
    }

    public function getSugars(): ?int
    {
        return $this->sugars;
    }

    public function setSugars(int $sugars): static
    {
        $this->sugars = $sugars;

        return $this;
    }

    public function getPotass(): ?int
    {
        return $this->potass;
    }

    public function setPotass(?int $potass): static
    {
        $this->potass = $potass;

        return $this;
    }

    public function getVitamins(): ?int
    {
        return $this->vitamins;
    }

    public function setVitamins(int $vitamins): static
    {
        $this->vitamins = $vitamins;

        return $this;
    }

    public function getShelf(): ?int
    {
        return $this->shelf;
    }

    public function setShelf(int $shelf): static
    {
        $this->shelf = $shelf;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getCups(): ?float
    {
        return $this->cups;
    }

    public function setCups(float $cups): static
    {
        $this->cups = $cups;

        return $this;
    }

    // public function getRating(): ?float
    // {
    //     return $this->rating;
    // }

    // public function setRating(?float $rating): static
    // {
    //     $this->rating = $rating;

    //     return $this;
    // }

    /**
     * 
     * @return null|Collection<int, ProductRating>
     */
    // public function getProductRatings(): ?Collection
    // {
    //     return $this->product_ratings;
    // }

    // public function getAverageProductRating(): ?float
    // {
    //     $ratings = $this->product_ratings;
    //     if($ratings === null || count($ratings) === 0) {
    //         return null;
    //     }
    //     $total = 0;
    //     $count = 0;
    //     foreach ($ratings as $rating) {
    //         $total += $rating->getRating();
    //         $count++;
    //     }
    //     return $total / $count;
    // }

    public function addProductRating(ProductRating $productRating): static
    {
        $this->product_ratings[] = $productRating;

        return $this;
    }

    // TODO: Make sure this avoids circular references
    // public function getProductImages(): ?Collection
    // {
    //     return $this->product_images;
    // }
}
