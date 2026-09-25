<?php

namespace App\Entity;

use App\Repository\AssigmentRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssigmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Assigment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assigments')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'assigments')]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'assigments')]
    private ?Fonction $fonction = null;

    #[ORM\Column]
    private ?\DateTime $dateStart = null;

    #[ORM\Column]
    private ?\DateTime $dateEnd = null;

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

    public function getDateStart(): ?\DateTime
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTime $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTime
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTime $dateEnd): static
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
}
