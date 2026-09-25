<?php

namespace App\Entity;

use App\Repository\FonctionRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FonctionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Fonction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column]
    private ?\DateTime $dateUpdate = null;

    /**
     * @var Collection<int, Assigment>
     */
    #[ORM\OneToMany(targetEntity: Assigment::class, mappedBy: 'fonction')]
    private Collection $assigments;

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
            $assigment->setFonction($this);
        }

        return $this;
    }

    public function removeAssigment(Assigment $assigment): static
    {
        if ($this->assigments->removeElement($assigment)) {
            // set the owning side to null (unless already changed)
            if ($assigment->getFonction() === $this) {
                $assigment->setFonction(null);
            }
        }

        return $this;
    }
}
