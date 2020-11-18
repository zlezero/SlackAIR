<?php

namespace App\Entity;

use App\Repository\MimeLabelsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MimeLabelsRepository::class)
 */
class MimeLabels
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $labelName;

    /**
     * @ORM\OneToMany(targetEntity=TypeMIME::class, mappedBy="label")
     */
    private $typeMimes;

    public function __construct()
    {
        $this->typeMimes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabelName(): ?string
    {
        return $this->labelName;
    }

    public function setLabelName(string $labelName): self
    {
        $this->labelName = $labelName;

        return $this;
    }

    /**
     * @return Collection|TypeMIME[]
     */
    public function getTypeMimes(): Collection
    {
        return $this->typeMimes;
    }

    public function addTypeMime(TypeMIME $typeMime): self
    {
        if (!$this->typeMimes->contains($typeMime)) {
            $this->typeMimes[] = $typeMime;
            $typeMime->setLabel($this);
        }

        return $this;
    }

    public function removeTypeMime(TypeMIME $typeMime): self
    {
        if ($this->typeMimes->removeElement($typeMime)) {
            // set the owning side to null (unless already changed)
            if ($typeMime->getLabel() === $this) {
                $typeMime->setLabel(null);
            }
        }

        return $this;
    }
}
