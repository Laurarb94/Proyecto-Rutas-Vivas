<?php

namespace App\Entity;

use App\Repository\PostalCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostalCodeRepository::class)]
class PostalCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $num_postal_code = null;

    //Relación con localidad. Muchos códigos postales pueden pertenecer a una única localidad
    #[ORM\ManyToOne(targetEntity: Localities::class, inversedBy: 'postalCodes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Localities $locality = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumPostalCode(): ?int
    {
        return $this->num_postal_code;
    }

    public function setNumPostalCode(?int $num_postal_code): static
    {
        $this->num_postal_code = $num_postal_code;

        return $this;
    }

    public function getLocality(): ?Localities
    {
        return $this->locality;
    }

    public function setLocality(?Localities $locality): static
    {
        $this->locality = $locality;
        return $this;
    }


    public function __toString()
    {
        return $this->getNumPostalCode();
    }

    
}
