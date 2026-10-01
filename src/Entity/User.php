<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const ROLE_COLLAB = 'ROLE_COLLAB';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_RETAURANT_OWNER = 'ROLE_RESTAURANT_OWNER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * $role
     *
     * @var string|null
     */
    #[NotBlank(
        message:'Le droit doit-être choisie',
        groups:['user:create']
    )]
    private ?string $role = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * $plainPassword
     *
     * @var string|null
     */
    #[NotBlank(
        message:'Le mot de passe doit-être saisie',
        groups:['user:create']
    )]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column]
    #[NotBlank(
        message:'Veuillez saisir une date de recrutement',
        groups:['user:create', 'Default']
    )]
    private ?\DateTime $dateRecruitment = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column]
    private ?\DateTime $dateUpdate = null;

    /**
     * @var Collection<int, Assigment>
     */
    #[ORM\OneToMany(targetEntity: Assigment::class, mappedBy: 'user')]
    private Collection $assigments;

    /**
     * @var Collection<int, Restaurant>
     */
    #[ORM\OneToMany(targetEntity: Restaurant::class, mappedBy: 'owner')]
    private Collection $restaurants;

    #[ORM\Column(nullable: true)]
    private ?bool $isAdmin = null;

    public function __construct()
    {
        $this->assigments = new ArrayCollection();
        $this->restaurants = new ArrayCollection();
    }

    public static function getAvailableRoles() : array
    {
        return [
            'Propriétaire' => self::ROLE_RETAURANT_OWNER,
            'Collaborateur' => self::ROLE_COLLAB,
        ];
    }
    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        return array_unique($roles);
    }

    public function getRole() : ?string
    {
        $roles = $this->roles;
        $this->role = ($roles[0]) ?? null;
        return $this->role;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function setRole(?string $role = null): static
    {
        $this->role = $role;
        $this->roles = [$role];
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getDateRecruitment(): ?\DateTime
    {
        return $this->dateRecruitment;
    }

    public function setDateRecruitment(\DateTime $dateRecruitment): static
    {
        $this->dateRecruitment = $dateRecruitment;

        return $this;
    }

    public function getDateCreate(): ?\DateTime
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTime $dateCreate): static
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function getDateUpdate(): ?\DateTime
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(\DateTime $dateUpdate): static
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->dateUpdate = new DateTime();
    }

    #[ORM\PrePersist]
    public function createTimestamp(): void
    {
        $this->dateCreate = new DateTime();
    }

    /**
     * @return Collection<int, Assigment>
     */
    public function getAssigments(): Collection
    {
        return $this->assigments;
    }

    public function addAssigment(Assigment $assigment): static
    {
        if (!$this->assigments->contains($assigment)) {
            $this->assigments->add($assigment);
            $assigment->setUser($this);
        }

        return $this;
    }

    public function removeAssigment(Assigment $assigment): static
    {
        if ($this->assigments->removeElement($assigment)) {
            // set the owning side to null (unless already changed)
            if ($assigment->getUser() === $this) {
                $assigment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Restaurant>
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): static
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants->add($restaurant);
            $restaurant->setOwner($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            // set the owning side to null (unless already changed)
            if ($restaurant->getOwner() === $this) {
                $restaurant->setOwner(null);
            }
        }

        return $this;
    }

    public function isAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(?bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function __toString()
    {
        return $this->lastname.' - '.$this->firstname;
    }
}
