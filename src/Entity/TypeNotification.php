<?php

namespace App\Entity;

use App\Repository\TypeNotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeNotificationRepository::class)
 */
class TypeNotification
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
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="typeNotification")
     */
    private $Notifications;

    public function __construct()
    {
        $this->Notifications = new ArrayCollection();
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

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->Notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->Notifications->contains($notification)) {
            $this->Notifications[] = $notification;
            $notification->setTypeNotification($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->Notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getTypeNotification() === $this) {
                $notification->setTypeNotification(null);
            }
        }

        return $this;
    }
}
