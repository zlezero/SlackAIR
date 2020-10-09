<?php

namespace App\Entity;

use App\Repository\TypeMIMERepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
    private $Label;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $Extension;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $TypeMIME;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="TypeMIMEId")
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

    public function getLabel(): ?string
    {
        return $this->Label;
    }

    public function setLabel(string $Label): self
    {
        $this->Label = $Label;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->Extension;
    }

    public function setExtension(string $Extension): self
    {
        $this->Extension = $Extension;

        return $this;
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
            $media->setTypeMIMEId($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->medias->contains($media)) {
            $this->medias->removeElement($media);
            // set the owning side to null (unless already changed)
            if ($media->getTypeMIMEId() === $this) {
                $media->setTypeMIMEId(null);
            }
        }

        return $this;
    }
}
