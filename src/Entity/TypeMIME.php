<?php

namespace App\Entity;

use App\Repository\TypeMIMERepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author ZONCHELLO Sébastien
 * Cette classe représente le type mime d'un média
 */

/**
 * @ORM\Entity(repositoryClass=TypeMIMERepository::class)
 */
class TypeMIME
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
    private $TypeMIME;

    /**
     * @ORM\ManyToOne(targetEntity=MimeLabels::class, inversedBy="typeMimes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $label;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="MimeType")
     */
    private $medias;

    public function __construct()
    {
        $this->medias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeMIME(): ?string
    {
        return $this->TypeMIME;
    }

    public function setTypeMIME(string $TypeMIME): self
    {
        $this->TypeMIME = $TypeMIME;

        return $this;
    }

    public function getLabel(): ?MimeLabels
    {
        return $this->label;
    }

    public function setLabel(?MimeLabels $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->medias->contains($media)) {
            $this->medias[] = $media;
            $media->setMimeType($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getMimeType() === $this) {
                $media->setMimeType(null);
            }
        }

        return $this;
    }
}
