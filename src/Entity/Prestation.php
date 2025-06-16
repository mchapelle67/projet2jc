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
    private Collection $prestation;

    #[ORM\Column(length: 255)]
    private ?string $nomPrestation = null;

    public function __construct()
    {
        $this->prestation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Devis>
     */
    public function getPrestation(): Collection
    {
        return $this->prestation;
    }

    public function addPrestation(Devis $prestation): static
    {
        if (!$this->prestation->contains($prestation)) {
            $this->prestation->add($prestation);
            $prestation->setPrestation($this);
        }

        return $this;
    }

    public function removePrestation(Devis $prestation): static
    {
        if ($this->prestation->removeElement($prestation)) {
            // set the owning side to null (unless already changed)
            if ($prestation->getPrestation() === $this) {
                $prestation->setPrestation(null);
            }
        }

        return $this;
    }

    public function getNomPrestation(): ?string
    {
        return $this->nomPrestation;
    }

    public function setNomPrestation(string $nomPrestation): static
    {
        $this->nomPrestation = $nomPrestation;

        return $this;
    }
}
