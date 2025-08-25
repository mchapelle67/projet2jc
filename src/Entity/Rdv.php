<?php

namespace App\Entity;

use App\Repository\RdvRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RdvRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Rdv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 150)]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_rdv = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_demande = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateModification = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'rdvs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prestation $prestation = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicule $vehicule = null;

    #[ORM\Column]
    private ?bool $rappel_rdv = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = strtoupper($nom);

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = ucfirst($prenom);

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getDateRdv(): ?\DateTimeImmutable
    {
        return $this->date_rdv;
    }

    public function setDateRdv(\DateTimeImmutable $date_rdv): static
    {
        $this->date_rdv = $date_rdv;

        return $this;
    }

    public function getDateDemande(): ?\DateTimeImmutable
    {
        return $this->date_demande;
    }

    #[ORM\PrePersist]    
    public function setDateDemande(): static
    {
        $this->date_demande = new DateTimeImmutable();

        return $this;
    }

    public function getDateModification(): ?\DateTimeImmutable
    {
        return $this->dateModification;
    }
    
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateModification(): static
    {
        $this->dateModification = new DateTimeImmutable();

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPrestation(): ?Prestation
    {
        return $this->prestation;
    }

    public function setPrestation(?Prestation $prestation): static
    {
        $this->prestation = $prestation;

        return $this;
    }

    public function getVehicule(): ?Vehicule
    {
        return $this->vehicule;
    }

    public function setVehicule(Vehicule $vehicule): static
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    public function isRappelRdv(): ?bool
    {
        return $this->rappel_rdv;
    }

    public function setRappelRdv(bool $rappel_rdv): static
    {
        $this->rappel_rdv = $rappel_rdv;

        return $this;
    }

      public function getSlug(): ?string
    {
        return $this->slug;
    }

    #[ORM\PrePersist]
    public function setSlug(): static
    {
            
        $this->slug = uniqid();
    
        return $this;
    }
}
