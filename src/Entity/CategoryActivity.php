<?php

namespace App\Entity;

use App\Repository\CategoryActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CategoryActivityRepository::class)]
class CategoryActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    //Relación con subcategoría
    #[ORM\OneToMany(targetEntity: SubcategoryActivity::class, mappedBy: 'categoryActivity', cascade:['persist', 'remove'] )]
    private Collection $subcategories;

    public function  __construct()
    {
        $this->subcategories = new ArrayCollection();
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

    public function getSubcategories(): Collection
    {
        return $this->subcategories;
    }

    public function addSubcategory(SubcategoryActivity $subcategory): static
    {
        if (!$this->subcategories->contains($subcategory)) {
            $this->subcategories->add($subcategory);
            $subcategory->setCategoryActivity($this);
        }

        return $this;
    }

    public function removeSubcategory(SubcategoryActivity $subcategory): static
    {
        if ($this->subcategories->removeElement($subcategory)) {
            // Si la relación es bidireccional, limpiar la referencia en SubcategoryActivity
            if ($subcategory->getCategoryActivity() === $this) {
                $subcategory->setCategoryActivity(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

}
