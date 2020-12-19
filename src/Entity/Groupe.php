<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author ZONCHELLO Sébastien
 * Cette classe représente un channel qui peut être soit public, privé ou un DM
 */

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

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="Groupe")
     */
    private $notifications;

    public function __construct(EntityManager $entityManager=null)
    {
            $this->invitations = new ArrayCollection();
            $this->messages = new ArrayCollection();
            $this->notifications = new ArrayCollection();
    }

    public function getFormattedGroupe() {
        return [
            "id" => $this->getid(),
            "nom" => $this->getNom(),
            "id_proprietaire" => $this->getIdProprietaire()->getId(),
            "date_creation" => $this->getDateCreation(),
            "description" => $this->getDescription(),
            "type_groupe" => $this->getTypeGroupeId()->getFormattedTypeGroupe()
        ];
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

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setGroupe($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getGroupe() === $this) {
                $notification->setGroupe(null);
            }
        }

        return $this;

    }

}
