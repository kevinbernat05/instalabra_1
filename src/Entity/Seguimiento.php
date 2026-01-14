<?php

namespace App\Entity;

use App\Repository\SeguimientoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeguimientoRepository::class)]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: "seguidor_seguido_unique", columns: ["seguidor_id", "seguido_id"])
])]
class Seguimiento
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: "seguimientosQueHace")]
    #[ORM\JoinColumn(nullable: false)]
    private $seguidor;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: "seguimientosQueRecibe")]
    #[ORM\JoinColumn(nullable: false)]
    private $seguido;

    #[ORM\Column(type: "datetime")]
    private $fechaSeguimiento;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeguidor(): ?Usuario
    {
        return $this->seguidor;
    }

    public function setSeguidor(?Usuario $seguidor): self
    {
        $this->seguidor = $seguidor;

        return $this;
    }

    public function getSeguido(): ?Usuario
    {
        return $this->seguido;
    }

    public function setSeguido(?Usuario $seguido): self
    {
        $this->seguido = $seguido;

        return $this;
    }

    public function getFechaSeguimiento(): ?\DateTimeInterface
    {
        return $this->fechaSeguimiento;
    }

    public function setFechaSeguimiento(\DateTimeInterface $fechaSeguimiento): self
    {
        $this->fechaSeguimiento = $fechaSeguimiento;

        return $this;
    }
}
