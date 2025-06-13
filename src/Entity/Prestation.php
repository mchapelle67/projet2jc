<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrestationRepository::class)]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Devis>
     */
    #[ORM\OneToMany(targetEntity: Devis::class, mappedBy: 'prestation')]
    private Collection $nomPrestation;

    public function __construct()
    {
        $this->nomPrestation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Devis>
     */
    public function getNomPrestation(): Collection
    {
        return $this->nomPrestation;
    }

    public function addNomPrestation(Devis $nomPrestation): static
    {
        if (!$this->nomPrestation->contains($nomPrestation)) {
            $this->nomPrestation->add($nomPrestation);
            $nomPrestation->setPrestation($this);
        }

        return $this;
    }

    public function removeNomPrestation(Devis $nomPrestation): static
    {
        if ($this->nomPrestation->removeElement($nomPrestation)) {
            // set the owning side to null (unless already changed)
            if ($nomPrestation->getPrestation() === $this) {
                $nomPrestation->setPrestation(null);
            }
        }

        return $this;
    }
}
