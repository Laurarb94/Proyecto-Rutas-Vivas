<?php

namespace App\Entity;

use App\Entity\Localities;

use App\Repository\ProvincesRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProvincesRepository::class)]
class Provinces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    //Relación con localidades, una provincia puede tener muchas localidades, oneTomany
    #[ORM\OneToMany(targetEntity: Localities::class, mappedBy: 'province', cascade: ['persist', 'remove'])]
    private Collection $localities;

    public function __construct()
    {
        $this->localities = new ArrayCollection();
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

    public function getLocalities(): Collection
    {
        return $this->localities;
    }

    public function addLocality(Localities $locality): static
    {
        if(!$this->localities->contains($locality)){
            $this->localities->add($locality);
            $locality->setProvince($this);
        }

        return $this;
    }

    public function removeLocality(Localities $locality): static
    {
        if ($this->localities->removeElement($locality)) {
            if ($locality->getProvince() === $this) {
                $locality->setProvince(null);
            }
        }

        return $this;
    }


    public function __toString()
    {
        return $this->getName();
    }



}
