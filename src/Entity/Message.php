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
     * @ORM\OneToOne(targetEntity=Media::class, cascade={"persist", "remove"})
     */
    private $Media;

    /**
     * @ORM\Column(type="boolean")
     */
    private $EstEpingle;

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

    public function getMedia(): ?media
    {
        return $this->Media;
    }

    public function setMedia(?media $Media): self
    {
        $this->Media = $Media;

        return $this;
    }

    public function getFormattedMessage(){
        return [
            'messageId' => $this->getId(),
            'messageTime' => date_format($this->getDateEnvoi(), 'r'),
            'message' => $this->getTexte(),
            'media' => $this->getMedia() ? $this->getMedia()->getFormattedMedia() : null,
            'pseudo' => $this->getUserId()->getPseudo(),
            'clientId' => $this->getUserId()->getId(),
            'channel' => $this->getGroupeId()->getId(),
            'photo_de_profile' => $this->getUserId()->getFileName()

        ];
    }

    public function getEstEpingle(): ?bool
    {
        return $this->EstEpingle;
    }

    public function setEstEpingle(bool $EstEpingle): self
    {
        $this->EstEpingle = $EstEpingle;

        return $this;
    }

}
