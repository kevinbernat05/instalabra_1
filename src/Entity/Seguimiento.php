<?php

namespace App\Entity;
use App\Repository\SeguimientoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name:"seguidor_seguido_unique", columns:["seguidor_id","seguido_id"])
])]
class Seguimiento
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity:Usuario::class, inversedBy:"seguimientosQueHace")]
    #[ORM\JoinColumn(nullable:false)]
    private Usuario $seguidor;

    #[ORM\ManyToOne(targetEntity:Usuario::class, inversedBy:"seguimientosQueRecibe")]
    #[ORM\JoinColumn(nullable:false)]
    private Usuario $seguido;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $fechaSeguimiento;
}
