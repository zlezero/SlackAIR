<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 */
class Media
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TypeMIME::class, inversedBy="medias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $TypeMIMEId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Filename;

    /**
     * @ORM\Column(type="float")
     */
    private $Size;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $HASH;

    /**
     * @ORM\ManyToOne(targetEntity=Message::class, inversedBy="Medias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $MessageId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeMIMEId(): ?TypeMIME
    {
        return $this->TypeMIMEId;
    }

    public function setTypeMIMEId(?TypeMIME $TypeMIMEId): self
    {
        $this->TypeMIMEId = $TypeMIMEId;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->Filename;
    }

    public function setFilename(string $Filename): self
    {
        $this->Filename = $Filename;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->Size;
    }

    public function setSize(float $Size): self
    {
        $this->Size = $Size;

        return $this;
    }

    public function getHASH(): ?string
    {
        return $this->HASH;
    }

    public function setHASH(string $HASH): self
    {
        $this->HASH = $HASH;

        return $this;
    }

    public function getMessageId(): ?Message
    {
        return $this->MessageId;
    }

    public function setMessageId(?Message $MessageId): self
    {
        $this->MessageId = $MessageId;

        return $this;
    }
}
