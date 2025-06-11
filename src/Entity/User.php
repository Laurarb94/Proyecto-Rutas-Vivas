<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Ya existe una cuenta con este mail, por favor introduce otro')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['userName'])]
#[UniqueEntity(fields: ['userName'], message: 'Nombre de usuario no disponible. Por favor introduce otro')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private string $clerkId;

    #[ORM\Column(length: 100, type:'string', nullable:true)]
    private ?string $userName = null;

    #[ORM\Column(length: 100, type: 'string', nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 100, type: 'string', nullable: true)]
    private ?string $firstLastName = null;

    #[ORM\Column(length: 100, type: 'string', nullable: true)]
    private ?string $secondLastName = null;

    #[ORM\Column(length: 150)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 100, type: 'string', nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column('boolean')]
    private bool $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $activationToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addres = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    //Relación con provincias: un usuario pertenece a una provincia, manytoone
    #[ORM\ManyToOne(targetEntity: Provinces::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Provinces $province;

    //Relación con localidades: un usuario pertenece a una localidad, manytoone
    #[ORM\ManyToOne(targetEntity: Localities::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Localities $locality = null;

    //Relación con el código postal, un usuario pertenece a un código postal
    #[ORM\ManyToOne(targetEntity: PostalCode::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PostalCode $postalCode;

    //RElación con los mensajes
    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class)]
    private Collection $sentMessages;


    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Message::class)]
    private Collection $receivedMessages;

    //RElación con los seguidores
    #[ORM\OneToMany(mappedBy: 'follower', targetEntity: UserFollow::class)]
    private Collection $follows;

    #[ORM\OneToMany(mappedBy: 'followed', targetEntity: UserFollow::class)]
    private Collection $followers;

    //propiedad auxiliar para luego interactuar en el formulario de edición
    private ?File $photoFile = null;

    //Token para el reseteo de la contraseña
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $tokenExpiration = null;

    //Añado la relación inversa con PErsonalActivities
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PersonalActivities::class)]
    private Collection $personalActivities;

    #[ORM\Column(type: 'boolean')]
    private bool $isProfileCompleted = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isPrivate = true;

    //Relación inversa con notificaciones
    #[ORM\OneToMany(mappedBy: "user", targetEntity: UserNotification::class, cascade: ["remove"], orphanRemoval: true)]
    private Collection $notifications;


    public function __construct()
    {
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->follows = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->personalActivities = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClerkId(): string
    {
        return $this->clerkId;
    }

    public function setClerkId(string $clerkId)
    {
        $this->clerkId = $clerkId;
        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;

        return $this;
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

    public function getFirstLastName(): ?string
    {
        return $this->firstLastName;
    }

    public function setFirstLastName(string $firstLastName): static
    {
        $this->firstLastName = $firstLastName;

        return $this;
    }

    public function getSecondLastName(): ?string
    {
        return $this->secondLastName;
    }

    public function setSecondLastName(string $secondLastName): static
    {
        $this->secondLastName = $secondLastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(?string $activationToken): static
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    public function getAddres(): ?string
    {
        return $this->addres;
    }

    public function setAddres(?string $addres): static
    {
        $this->addres = $addres;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
        
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getProvince(): ?Provinces
    {
        return $this->province;
    }

    public function setProvince(Provinces $province): static
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

    public function getPostalCode(): ?PostalCode
    {
        return $this->postalCode;
    }

    public function setPostalCode(PostalCode $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function getFollows(): Collection
    {
        return $this->follows;
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    //Métodos para poder implementar userInterface y PasswordHasher y que en Register no de error
    public function eraseCredentials(): void
    {
        
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function __toString(): string
    {
        return $this->getName() ?? 'Usuario';
    }

    /*
    public function __toString()
    {
        return $this->getName();
    }
        */


    //método para interactuar con ficheros en el formulario de edición
    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function setPhotoFile(?File $file): void
    {
        $this->photoFile = $file;
    }

    //Métodos para el token de reseteo de la contraseña
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getTokenExpiration(): ?\DateTimeInterface
    {
        return $this->tokenExpiration;
    }

    public function setTokenExpiration(?\DateTimeInterface $expiration): self
    {
        $this->tokenExpiration = $expiration;
        return $this;
    }

    //Relación con personalActivities.
    public function getPersonalActivities(): Collection
    {
        return $this->personalActivities;
    }

    public function addPersonalActivity(PersonalActivities $personalActivity): self
    {
        if (!$this->personalActivities->contains($personalActivity)) {
            $this->personalActivities[] = $personalActivity;
            $personalActivity->setUser($this);
        }

        return $this;
    }

    public function removePersonalActivity(PersonalActivities $personalActivity): self
    {
        if ($this->personalActivities->contains($personalActivity)) {
            $this->personalActivities->removeElement($personalActivity);
            // si se elimina la actividad, también se debe eliminar la relación inversa
            if ($personalActivity->getUser() === $this) {
                $personalActivity->setUser(null);
            }
        }

        return $this;
    }

    public function isProfileCompleted(): bool
    {
       // return $this->isProfileCompleted;
        return 
            $this->getUserName() !== null &&
            $this->getName() !== null &&
            $this->getFirstLastName() !== null &&
            $this->getSecondLastName() !== null &&
            $this->getPhone() !== null &&
            $this->getEmail() !== null &&
            $this->getBiography() !== null &&
            $this->getProvince() !== null &&
            $this->getLocality() !== null &&
            $this->getAddres() !== null;
    }
    
    public function setIsProfileCompleted(bool $completed): self
    {
        $this->isProfileCompleted = $completed;
        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;
        return $this;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

}
