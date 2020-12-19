<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author ZONCHELLO Sébastien
 * Cette classe représente une notification
 */

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $DateNotification;

    /**
     * @ORM\ManyToOne(targetEntity=Groupe::class, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Groupe;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Utilisateur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $EstLue;

    /**
     * @ORM\Column(type="integer")
     */
    private $NbMsg;

    /**
     * @ORM\ManyToOne(targetEntity=TypeNotification::class, inversedBy="Notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeNotification;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateNotification(): ?\DateTimeInterface
    {
        return $this->DateNotification;
    }

    public function setDateNotification(\DateTimeInterface $DateNotification): self
    {
        $this->DateNotification = $DateNotification;

        return $this;
    }

    public function getGroupe(): ?Groupe
    {
        return $this->Groupe;
    }

    public function setGroupe(?Groupe $Groupe): self
    {
        $this->Groupe = $Groupe;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->Utilisateur;
    }

    public function setUtilisateur(?User $Utilisateur): self
    {
        $this->Utilisateur = $Utilisateur;

        return $this;
    }

    public function getEstLue(): ?bool
    {
        return $this->EstLue;
    }

    public function setEstLue(bool $EstLue): self
    {
        $this->EstLue = $EstLue;

        return $this;
    }

    public function getNbMsg(): ?int
    {
        return $this->NbMsg;
    }

    public function setNbMsg(int $NbMsg): self
    {
        $this->NbMsg = $NbMsg;

        return $this;
    }

    public function getTypeNotification(): ?TypeNotification
    {
        return $this->typeNotification;
    }

    public function setTypeNotification(?TypeNotification $typeNotification): self
    {
        $this->typeNotification = $typeNotification;

        return $this;
    }
}
