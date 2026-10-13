<?php

namespace App\Entity;

use App\Repository\FonctionRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: FonctionRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['name'],
    message: 'Une fonction avec ce nom existe déjà.'
)]
class Fonction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[NotBlank(message:'Veuillez saisir un nom de fonction')]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column]
    private ?\DateTime $dateUpdate = null;

    /**
     * @var Collection<int, Assignment>
     */
    #[ORM\OneToMany(targetEntity: Assignment::class, mappedBy: 'fonction')]
    private Collection $assignments;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateArchived = null;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
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
     * @return Collection<int, Assignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(Assignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setFonction($this);
        }

        return $this;
    }

    public function removeAssignment(Assignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getFonction() === $this) {
                $assignment->setFonction(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return ucFirst($this->name);
    }

    public function getDateArchived(): ?\DateTime
    {
        return $this->dateArchived;
    }

    public function setDateArchived(?\DateTime $dateArchived): static
    {
        $this->dateArchived = $dateArchived;

        return $this;
    }
}
