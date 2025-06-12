<?php

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $img = null;

    /**
     * @var Collection<int, VO>
     */
    #[ORM\OneToMany(targetEntity: VO::class, mappedBy: 'photo')]
    private Collection $vo;

    public function __construct()
    {
        $this->vo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(string $img): static
    {
        $this->img = $img;

        return $this;
    }

    /**
     * @return Collection<int, VO>
     */
    public function getVo(): Collection
    {
        return $this->vo;
    }

    public function addVo(VO $vo): static
    {
        if (!$this->vo->contains($vo)) {
            $this->vo->add($vo);
            $vo->setPhoto($this);
        }

        return $this;
    }

    public function removeVo(VO $vo): static
    {
        if ($this->vo->removeElement($vo)) {
            // set the owning side to null (unless already changed)
            if ($vo->getPhoto() === $this) {
                $vo->setPhoto(null);
            }
        }

        return $this;
    }
}
