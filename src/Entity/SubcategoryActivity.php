<?php

namespace App\Entity;

use App\Repository\SubcategoryActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubcategoryActivityRepository::class)]
class SubcategoryActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    //Relación con la categoría
    #[ORM\ManyToOne(targetEntity: CategoryActivity::class, inversedBy: 'subcategories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryActivity $categoryActivity = null;

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

    public function getCategoryActivity(): ?CategoryActivity
    {
        return $this->categoryActivity;
    }

    public function setCategoryActivity(?CategoryActivity $categoryActivity): static
    {
        $this->categoryActivity = $categoryActivity;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;  
    }
    
}
