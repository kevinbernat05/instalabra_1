<?php

namespace App\Entity;

use App\Repository\PalabraRepository;
use Doctrine\DBAL\Types\Types;
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
    private Collection $comentarios;

    #[ORM\OneToMany(mappedBy:"palabra", targetEntity:Valoracion::class)]
    private Collection $valoraciones;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $fechaCreacion;

    #[ORM\Column(type:"integer")]
    private int $contadorValoraciones;

    public function __construct() {
        $this->comentarios = new ArrayCollection();
        $this->valoraciones = new ArrayCollection();
    }
}
