<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[NotBlank(message:'Veuillez saisir le nom')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[NotBlank(message:'Veuillez saisir une address')]
    private ?string $address = null;

    #[ORM\Column]
    #[NotBlank(message:'Veuillez saisir un code postal')]
    private ?int $zipCode = null;


    #[ORM\Column(length: 255)]
    #[NotBlank(message:'Veuillez saisir une ville')]
    private ?string $city = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column]
    private ?\DateTime $dateUpdate = null;

    /**
     * @var Collection<int, Assigment>
     */
    #[ORM\OneToMany(targetEntity: Assigment::class, mappedBy: 'restaurant')]
    private Collection $assigments;

    #[ORM\ManyToOne(inversedBy: 'restaurants')]
    #[NotBlank(message:'Veuillez choisir un propriétaire')]
    private ?User $owner = null;

    public function __construct()
    {
        $this->assigments = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zipCode;
    }

    public function setZipCode(int $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getPotalCode() : ?string
    {
        return ($this->zipCode !== null) ? str_pad((string)$this->zipCode, 5, '0', STR_PAD_LEFT) : '';
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

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
            $assigment->setRestaurant($this);
        }

        return $this;
    }

    public function removeAssigment(Assigment $assigment): static
    {
        if ($this->assigments->removeElement($assigment)) {
            // set the owning side to null (unless already changed)
            if ($assigment->getRestaurant() === $this) {
                $assigment->setRestaurant(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function __toString()
    {
        return ucFirst($this->name);
    }
}
