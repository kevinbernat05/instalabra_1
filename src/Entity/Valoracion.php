<?php

namespace App\Entity;

use App\Repository\ValoracionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ValoracionRepository::class)]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: "usuario_palabra_unique", columns: ["usuario_id", "palabra_id"])
])]
class Valoracion
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity:Usuario::class, inversedBy:"valoraciones")]
    #[ORM\JoinColumn(nullable:false)]
    private Usuario $usuario;

    #[ORM\ManyToOne(targetEntity:Palabra::class, inversedBy:"valoraciones")]
    #[ORM\JoinColumn(nullable:false)]
    private Palabra $palabra;

    #[ORM\Column(type:"boolean")]
    private bool $likeActiva = true;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $fechaCreacion;

    // ------------------- GETTERS & SETTERS -------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getPalabra(): Palabra
    {
        return $this->palabra;
    }

    public function setPalabra(Palabra $palabra): self
    {
        $this->palabra = $palabra;
        return $this;
    }

    public function isLikeActiva(): bool
    {
        return $this->likeActiva;
    }

    public function setLikeActiva(bool $likeActiva): self
    {
        $this->likeActiva = $likeActiva;
        return $this;
    }

    public function getFechaCreacion(): \DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;
        return $this;
    }
}
