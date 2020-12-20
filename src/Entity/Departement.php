<?php

namespace App\Entity;

use App\Repository\DepartementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author ZONCHELLO Sébastien
 * Cette classe représente le département d'un utilisateur (employé)
 */

/**
 * @ORM\Entity(repositoryClass=DepartementRepository::class)
 */
class Departement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $Nom;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="DepartementId")
     */
    private $Personnel;

    public function __construct()
    {
        $this->Personnel = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): self
    {
        $this->Nom = $Nom;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getPersonnel(): Collection
    {
        return $this->Personnel;
    }

    public function addPersonnel(User $personnel): self
    {
        if (!$this->Personnel->contains($personnel)) {
            $this->Personnel[] = $personnel;
            $personnel->setDepartementId($this);
        }

        return $this;
    }

    public function removePersonnel(User $personnel): self
    {
        if ($this->Personnel->contains($personnel)) {
            $this->Personnel->removeElement($personnel);
            // set the owning side to null (unless already changed)
            if ($personnel->getDepartementId() === $this) {
                $personnel->setDepartementId(null);
            }
        }

        return $this;
    }

    public function getFormattedDepartement() {
        return [
            "id" => $this->getId(),
            "nom" => $this->getNom()
        ];
    }
}
