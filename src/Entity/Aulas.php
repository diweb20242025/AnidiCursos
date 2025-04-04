<?php

namespace App\Entity;

use App\Repository\AulasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AulasRepository::class)]
class Aulas
{

    #[ORM\Id]
    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1)]
    private ?string $codigo = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $capacidad = null;

    #[ORM\Column]
    private ?bool $adaptado = null;

    /**
     * @var Collection<int, Cursos>
     */
    #[ORM\OneToMany(targetEntity: Cursos::class, 
    mappedBy: 'codAula')]
    private Collection $cursos;

    public function __construct()
    {
        $this->cursos = new ArrayCollection();
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getCapacidad(): ?int
    {
        return $this->capacidad;
    }

    public function setCapacidad(int $capacidad): static
    {
        $this->capacidad = $capacidad;

        return $this;
    }

    public function isAdaptado(): ?bool
    {
        return $this->adaptado;
    }

    public function setAdaptado(bool $adaptado): static
    {
        $this->adaptado = $adaptado;

        return $this;
    }

    /**
     * @return Collection<int, Cursos>
     */
    public function getCursos(): Collection
    {
        return $this->cursos;
    }

    public function addCurso(Cursos $curso): static
    {
        if (!$this->cursos->contains($curso)) {
            $this->cursos->add($curso);
            $curso->setCodAula($this);
        }

        return $this;
    }

    public function removeCurso(Cursos $curso): static
    {
        if ($this->cursos->removeElement($curso)) {
            // set the owning side to null (unless already changed)
            if ($curso->getCodAula() === $this) {
                $curso->setCodAula(null);
            }
        }

        return $this;
    }
}
