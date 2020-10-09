<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GroupeRepository::class)
 */
class Groupe
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
    private $Nom;

    /**
     * @ORM\ManyToOne(targetEntity=TypeGroupe::class, inversedBy="groupes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $TypeGroupeId;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="GroupesCrees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $IdProprietaire;

    /**
     * @ORM\Column(type="datetime")
     */
    private $DateCreation;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="GroupeId")
     */
    private $invitations;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="GroupeId")
     */
    private $messages;

    public function __construct()
    {
        $this->invitations = new ArrayCollection();
        $this->messages = new ArrayCollection();
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

    public function getTypeGroupeId(): ?TypeGroupe
    {
        return $this->TypeGroupeId;
    }

    public function setTypeGroupeId(?TypeGroupe $TypeGroupeId): self
    {
        $this->TypeGroupeId = $TypeGroupeId;

        return $this;
    }

    public function getIdProprietaire(): ?User
    {
        return $this->IdProprietaire;
    }

    public function setIdProprietaire(?User $IdProprietaire): self
    {
        $this->IdProprietaire = $IdProprietaire;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->DateCreation;
    }

    public function setDateCreation(\DateTimeInterface $DateCreation): self
    {
        $this->DateCreation = $DateCreation;

        return $this;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations[] = $invitation;
            $invitation->setGroupeId($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->contains($invitation)) {
            $this->invitations->removeElement($invitation);
            // set the owning side to null (unless already changed)
            if ($invitation->getGroupeId() === $this) {
                $invitation->setGroupeId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setGroupeId($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getGroupeId() === $this) {
                $message->setGroupeId(null);
            }
        }

        return $this;
    }

}
