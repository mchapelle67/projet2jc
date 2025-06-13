<?php

namespace App\Entity;

use App\Repository\VehiculeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiculeRepository::class)]
class Vehicule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $km = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $anneeFabrication = null;

    #[ORM\Column(length: 100)]
    private ?string $marque = null;

    #[ORM\Column(length: 150)]
    private ?string $modele = null;

    #[ORM\ManyToOne(inversedBy: 'vehicule')]
    private ?Carburant $carburant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKm(): ?int
    {
        return $this->km;
    }

    public function setKm(?int $km): static
    {
        $this->km = $km;

        return $this;
    }

    public function getAnneeFabrication(): ?\DateTime
    {
        return $this->anneeFabrication;
    }

    public function setAnneeFabrication(?\DateTime $anneeFabrication): static
    {
        $this->anneeFabrication = $anneeFabrication;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getCarburant(): ?Carburant
    {
        return $this->carburant;
    }

    public function setCarburant(?Carburant $carburant): static
    {
        $this->carburant = $carburant;

        return $this;
    }
}
