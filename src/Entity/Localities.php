<?php

namespace App\Entity;

use App\Entity\PostalCode;

use App\Repository\LocalitiesRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: LocalitiesRepository::class)]
class Localities
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    //Relación con provincias: muchas localidades pueden pertenecer a una provincia, manytoone
    #[ORM\ManyToOne(targetEntity: Provinces::class, inversedBy: 'localities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Provinces $province = null;

    //Relación con código postal. Una localidad puede tener muchos códigos postales, oneTomany
    #[ORM\OneToMany(targetEntity: PostalCode::class, mappedBy: 'locality', cascade: ['persist', 'remove'])]
    private Collection $postalCodes;

    public function  __construct()
    {
        $this->postalCodes = new ArrayCollection();
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

    public function getProvince(): ?Provinces
    {
        return $this->province;
    }

    public function setProvince(?Provinces $province): static
    {
        $this->province = $province;

        return $this;
    }

    public function getPostalCode(): Collection
    {
        return $this->postalCodes;
    }

    public function addPostalCode(PostalCode $postalCode): static
    {
        if (!$this->postalCodes->contains($postalCode)) {
            $this->postalCodes->add($postalCode);
            $postalCode->setLocality($this);
        }

        return $this;
    }

    public function removePostalCode(PostalCode $postalCode): static
    {
        if ($this->postalCodes->removeElement($postalCode)) {
            if ($postalCode->getLocality() === $this) {
                $postalCode->setLocality(null);
            }
        }

        return $this;
    }


    public function __toString()
    {
        return $this->getName();
    }
}
