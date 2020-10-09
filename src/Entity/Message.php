<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
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
    private $Texte;

    /**
     * @ORM\Column(type="datetime")
     */
    private $DateEnvoi;

    /**
     * @ORM\Column(type="boolean")
     */
    private $EstEfface;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $UserId;

    /**
     * @ORM\ManyToOne(targetEntity=Groupe::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $GroupeId;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="MessageId")
     */
    private $Medias;

    public function __construct()
    {
        $this->Medias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->Texte;
    }

    public function setTexte(string $Texte): self
    {
        $this->Texte = $Texte;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->DateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $DateEnvoi): self
    {
        $this->DateEnvoi = $DateEnvoi;

        return $this;
    }

    public function getEstEfface(): ?bool
    {
        return $this->EstEfface;
    }

    public function setEstEfface(bool $EstEfface): self
    {
        $this->EstEfface = $EstEfface;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->UserId;
    }

    public function setUserId(?User $UserId): self
    {
        $this->UserId = $UserId;

        return $this;
    }

    public function getGroupeId(): ?Groupe
    {
        return $this->GroupeId;
    }

    public function setGroupeId(?Groupe $GroupeId): self
    {
        $this->GroupeId = $GroupeId;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedias(): Collection
    {
        return $this->Medias;
    }

    public function addMedia(Media $media): self
    {
        if (!$this->Medias->contains($media)) {
            $this->Medias[] = $media;
            $media->setMessageId($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): self
    {
        if ($this->Medias->contains($media)) {
            $this->Medias->removeElement($media);
            // set the owning side to null (unless already changed)
            if ($media->getMessageId() === $this) {
                $media->setMessageId(null);
            }
        }

        return $this;
    }
}
