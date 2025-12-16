<?php

namespace App\Entity;

use App\Repository\ComentarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComentarioRepository::class)]
class Comentario
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity:Usuario::class, inversedBy:"comentarios")]
    #[ORM\JoinColumn(nullable:false)]
    private Usuario $usuario;

    #[ORM\ManyToOne(targetEntity:Palabra::class, inversedBy:"comentarios")]
    #[ORM\JoinColumn(nullable:false)]
    private Palabra $palabra;

    #[ORM\Column(type:"text")]
    private string $texto;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $fechaCreacion;
}
