<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sexe;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $apiToken;

    /**
     * @ORM\ManyToOne(targetEntity=Departement::class, inversedBy="Personnel")
     */
    private $DepartementId;

    /**
     * @ORM\OneToMany(targetEntity=Groupe::class, mappedBy="IdProprietaire")
     */
    private $GroupesCrees;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="UserId")
     */
    private $invitations;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="UserId")
     */
    private $messages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profession;

    /**
     * @ORM\ManyToOne(targetEntity=Statut::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $statut;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $firstConnection;

    /**
     * @ORM\ManyToOne(targetEntity=Groupe::class)
     */
    private $DernierGroupe;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $FileName;


    public function __construct()
    {
        $this->GroupesCrees = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function generateApiToken() {
        $this->setApiToken($this->generateString(32));
    }

    private function generateString(int $length = 64, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') : string {

        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);

    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getUsername() : string {
        return (string) $this->getEmail();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getDepartementId(): ?Departement
    {
        return $this->DepartementId;
    }

    public function setDepartementId(?Departement $DepartementId): self
    {
        $this->DepartementId = $DepartementId;

        return $this;
    }

    /**
     * @return Collection|Groupe[]
     */
    public function getGroupesCrees(): Collection
    {
        return $this->GroupesCrees;
    }

    public function addGroupesCree(Groupe $groupesCree): self
    {
        if (!$this->GroupesCrees->contains($groupesCree)) {
            $this->GroupesCrees[] = $groupesCree;
            $groupesCree->setIdProprietaire($this);
        }

        return $this;
    }

    public function removeGroupesCree(Groupe $groupesCree): self
    {
        if ($this->GroupesCrees->contains($groupesCree)) {
            $this->GroupesCrees->removeElement($groupesCree);
            // set the owning side to null (unless already changed)
            if ($groupesCree->getIdProprietaire() === $this) {
                $groupesCree->setIdProprietaire(null);
            }
        }

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
            $invitation->setUserId($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->contains($invitation)) {
            $this->invitations->removeElement($invitation);
            // set the owning side to null (unless already changed)
            if ($invitation->getUserId() === $this) {
                $invitation->setUserId(null);
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
            $message->setUserId($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getUserId() === $this) {
                $message->setUserId(null);
            }
        }

        return $this;
    }

    public function getStatut(): ?statut
    {
        return $this->statut;
    }

    public function setStatut(?statut $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getFirstConnection(): ?bool
    {
        return $this->firstConnection;
    }

    public function setFirstConnection(bool $firstConnection): self
    {
        $this->firstConnection = $firstConnection;
        return $this;
    }

    public function getDernierGroupe(): ?Groupe
    {
        return $this->DernierGroupe;
    }

    public function setDernierGroupe(?Groupe $DernierGroupe): self
    {
        $this->DernierGroupe = $DernierGroupe;
        return $this;
    }

    public function getFormattedUser() {
        return [
            "id" => $this->getId(),
            "prenom" => $this->getPrenom(),
            "nom" => $this->getNom(),
            "pseudo" => $this->getPseudo(),
            "profession" => $this->getProfession(),
            "statut" => $this->getStatut()->getFormattedStatus(),
            "photo_de_profile" => $this->getFileName(),
        ];
    }
    public function getFileName(): ?string
    {
        return '/uploads/pdp/'.($this->FileName ? $this->FileName : 'default.jpg');
    }

    public function setFileName(?string $FileName): self
    {
        $this->FileName = $FileName;
        return $this;
    }

}
