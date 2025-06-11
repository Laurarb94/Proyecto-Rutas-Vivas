<?php

namespace App\Entity;

use App\Repository\ImagesActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagesActivityRepository::class)]
class ImagesActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $image_url = null;

    //Relación con PersonalActivities: una foto pertenece a una actividad
    #[ORM\ManyToOne(targetEntity: PersonalActivities::class, inversedBy: 'imagesActivity')]
    private ?PersonalActivities $personalActivity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }

    public function getPersonalActivity(): ?PersonalActivities
    {
        return $this->personalActivity;
    }
    public function setPersonalActivity(?PersonalActivities $personalActivity): static
    {
        $this->personalActivity = $personalActivity;
        return $this;
    }

}
