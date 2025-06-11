<?php

namespace App\Entity;

use App\Repository\PersonalActivitiesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: PersonalActivitiesRepository::class)]
class PersonalActivities
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name_activity = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(nullable: true)]
    private ?int $valoration = null;

    //Relación con usuarios, un usuario podrá hacer muchas actividades
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'personalActivities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    //RElación con provincias, una actividad se hizo en una provincia
    #[ORM\ManyToOne(targetEntity: Provinces::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Provinces $province = null;

    //Relación con localidades, una actividad se hizo en una localidad
    #[ORM\ManyToOne(targetEntity: Localities::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Localities $locality = null;

    //Relación con las categorías, una actividad pertenece a una categoría
    #[ORM\ManyToOne(targetEntity: CategoryActivity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryActivity $categoryActivity = null;

    //Relación con subcategoría, una actividad pertenece a una suctageorí
    #[ORM\ManyToOne(targetEntity: SubcategoryActivity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubcategoryActivity $subcategoryActivity = null;

    //Relación con las fotos, una actividad podrá tener muchas fotos
    #[ORM\OneToMany(targetEntity: ImagesActivity::class, mappedBy: 'personalActivity', cascade: ['persist', 'remove'])]
    private Collection $imagesActivity;

    //Acompañante a la actividad
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $companion = null;

    //Si la activdad está o no completada para poder mostrarla luego en el twig
    #[ORM\Column(type: 'boolean')]
    private bool $completed = false;

    public function __construct()
    {
        $this->imagesActivity = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameActivity(): ?string
    {
        return $this->name_activity;
    }

    public function setNameActivity(string $name_activity): static
    {
        $this->name_activity = $name_activity;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

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

    public function getValoration(): ?int
    {
        return $this->valoration;
    }

    public function setValoration(?int $valoration): static
    {
        $this->valoration = $valoration;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getLocality(): ?Localities
    {
        return $this->locality;
    }

    public function setLocality(?Localities $locality): static
    {
        $this->locality = $locality;
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

    public function getSubcategoryActivity(): ?SubcategoryActivity
    {
        return $this->subcategoryActivity;
    }
    public function setSubcategoryActivity(?SubcategoryActivity $subcategoryActivity): static
    {
        $this->subcategoryActivity = $subcategoryActivity;
        return $this;
    }

    public function getImagesActivity(): Collection
    {
        return $this->imagesActivity;
    }




    public function addImagesActivity(imagesActivity $imagesActivity): static
    {
        if (!$this->imagesActivity->contains($imagesActivity)) {
            $this->imagesActivity->add($imagesActivity);
            $imagesActivity->setPersonalActivity($this);
        }
        return $this;
    }

    public function removeImagesActivity(imagesActivity $imagesActivity): static
    {
        if ($this->imagesActivity->removeElement($imagesActivity)) {
            if ($imagesActivity->getPersonalActivity() === $this) {
                $imagesActivity->setPersonalActivity(null);
            }
        }
        return $this;
    }

    public function getCompanion(): ?User
    {
        return $this->companion;
    }
    
    public function setCompanion(?User $user): self
    {
        $this->companion = $user;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }







}
