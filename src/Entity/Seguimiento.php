<?php

namespace App\Entity;
use App\Repository\SeguimientoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: "seguidor_seguido_unique", columns: ["seguidor_id", "seguido_id"])
])]
class Seguimiento
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: "seguimientosQueHace")]
    #[ORM\JoinColumn(nullable: false)]
    private Usuario $seguidor;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: "seguimientosQueRecibe")]
    #[ORM\JoinColumn(nullable: false)]
    private Usuario $seguido;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $fechaSeguimiento;

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

    public function getFechaSeguimiento(): \DateTimeInterface
    {
        return $this->fechaSeguimiento;
    }

    public function setFechaSeguimiento(\DateTimeInterface $fechaSeguimiento): self
    {
        $this->fechaSeguimiento = $fechaSeguimiento;
        return $this;
    }
}
