<?php

namespace App\Entity;

use App\Enum\AssisnmentStatus;
use App\Repository\AssigmentRepository;
use App\Validator\NoAssignmentOverlap;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: AssigmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[NoAssignmentOverlap(
    message:'Ce Collaborateur {{ collab }} est déjà plannifier sur toute ou en partie de la période sélectionnée',
    groups:['assignement:create', 'assignement:update']
)]
class Assigment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assigments')]
    #[NotBlank(message:'Veuillez choisir un utilisateur')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'assigments')]
    #[NotBlank(message:'Veuillez choisir un restaurant')]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'assigments')]
    #[NotBlank(message:'Veuillez choisir une fonction')]
    private ?Fonction $fonction = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[NotBlank(message:'Veuillez saisir une date de début')]
    #[GreaterThanOrEqual(
        'today',
        message: 'La date de début ne peut pas être antérieure à aujourd’hui.',
        groups: ['assignement:create',]
    )]
    private ?\DateTimeImmutable $dateStart = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[NotBlank(message:'Veuillez saisir une date de fin')]
    #[GreaterThan(
        propertyPath: 'dateStart',
        message: 'La date de fin doit être égale ou postérieure à la date de début.',
        groups: ['assignement:create',]
    )]
    private ?\DateTimeImmutable $dateEnd = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column]
    private ?\DateTime $dateUpdate = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getFonction(): ?Fonction
    {
        return $this->fonction;
    }

    public function setFonction(?Fonction $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getDateStart(): ?\DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeImmutable $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeImmutable $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

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

    public function getStatus(): ?AssisnmentStatus
    {
        $today = new DateTime('today');
        $status = AssisnmentStatus::EN_COURS;
        if ($this->dateStart > $today) {
            $status = AssisnmentStatus::EN_ATTENTE;
        }

        if ($this->dateEnd !== null && $this->dateEnd < $today) {
            $status = AssisnmentStatus::FINI;
        }
        return $status;
    }
}
