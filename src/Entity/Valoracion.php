<?php

namespace App\Entity;
use App\Repository\ValoracionRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name:"usuario_palabra_unique", columns:["usuario_id","palabra_id"])
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

    #[ORM\Column(type:"smallint")]
    #[Assert\Range(min:1, max:5)]
    private int $valor;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $fechaCreacion;
}
