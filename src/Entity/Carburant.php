<?php

namespace App\Entity;

use App\Repository\CarburantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarburantRepository::class)]
class Carburant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $typeCarburant = null;

    /**
     * @var Collection<int, Vehicule>
     */
    #[ORM\OneToMany(targetEntity: Vehicule::class, mappedBy: 'carburant')]
    private Collection $vehicule;

    public function __construct()
    {
        $this->vehicule = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeCarburant(): ?string
    {
        return $this->typeCarburant;
    }

    public function setTypeCarburant(string $typeCarburant): static
    {
        $this->typeCarburant = $typeCarburant;

        return $this;
    }

    /**
     * @return Collection<int, Vehicule>
     */
    public function getVehicule(): Collection
    {
        return $this->vehicule;
    }

    public function addVehicule(Vehicule $vehicule): static
    {
        if (!$this->vehicule->contains($vehicule)) {
            $this->vehicule->add($vehicule);
            $vehicule->setCarburant($this);
        }

        return $this;
    }

    public function removeVehicule(Vehicule $vehicule): static
    {
        if ($this->vehicule->removeElement($vehicule)) {
            // set the owning side to null (unless already changed)
            if ($vehicule->getCarburant() === $this) {
                $vehicule->setCarburant(null);
            }
        }

        return $this;
    }
}
