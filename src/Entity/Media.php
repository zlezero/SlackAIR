<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author ZONCHELLO Sébastien
 * Cette classe représente un média qui peut être envoyé en tant que message
 */

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
     * @ORM\Column(type="string", length=255)
     */
    private $Filename;

    /**
     * @ORM\Column(type="float")
     */
    private $Size;

    /**
     * @ORM\ManyToOne(targetEntity=TypeMIME::class, inversedBy="medias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $MimeType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return '/uploads/files/'.($this->Filename);
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

    public function getFormattedMedia(){
        return [
            'fileName' => $this->getFilename(),
            'fileMimeType' => $this->getMimeType()->getTypeMIME(),
            'fileLabel' => $this->getMimeType()->getLabel()->getLabelName(),
            'fileSize' => $this->getSize()
        ];
    }

    public function getMimeType(): ?TypeMIME
    {
        return $this->MimeType;
    }

    public function setMimeType(?TypeMIME $MimeType): self
    {
        $this->MimeType = $MimeType;

        return $this;
    }
}
