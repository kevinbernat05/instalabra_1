<?php

namespace App\Entity;

use App\Repository\PalabraRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PalabraRepository::class)]
class Palabra
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
    private int $id;

    #[ORM\Column(type:"text")]
    private string $texto;

    #[ORM\ManyToOne(targetEntity:Usuario::class, inversedBy:"palabras")]
    #[ORM\JoinColumn(nullable:false)]
    private Usuario $usuario;

    #[ORM\OneToMany(mappedBy:"palabra", targetEntity:Comentario::class)]
    #[ORM\OrderBy(["fechaCreacion" => "DESC"])]
    private Collection $comentarios;

    #[ORM\OneToMany(mappedBy:"palabra", targetEntity:Valoracion::class)]
    private Collection $valoraciones;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $fechaCreacion;

    public function __construct() {
        $this->comentarios = new ArrayCollection();
        $this->valoraciones = new ArrayCollection();
    }

    // ------------------- GETTERS & SETTERS -------------------

    public function getId(): ?int {
        return $this->id;
    }

    public function getTexto(): ?string {
        return $this->texto;
    }

    public function setTexto(string $texto): self {
        $this->texto = $texto;
        return $this;
    }

    public function getUsuario(): Usuario {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario): self {
        $this->usuario = $usuario;
        return $this;
    }

    public function getComentarios(): Collection {
        return $this->comentarios;
    }

    public function getValoraciones(): Collection {
        return $this->valoraciones;
    }

    public function getFechaCreacion(): \DateTimeInterface {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self {
        $this->fechaCreacion = $fechaCreacion;
        return $this;
    }
}
