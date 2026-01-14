<?php

namespace App\Entity;

use App\Repository\MensajeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MensajeRepository::class)]
class Mensaje
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mensajesEnviados')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $remitente = null;

    #[ORM\ManyToOne(inversedBy: 'mensajesRecibidos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $destinatario = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contenido = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaEnvio = null;

    #[ORM\Column]
    private bool $leido = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Palabra $palabraCompartida = null;

    public function __construct()
    {
        $this->fechaEnvio = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemitente(): ?Usuario
    {
        return $this->remitente;
    }

    public function setRemitente(?Usuario $remitente): static
    {
        $this->remitente = $remitente;

        return $this;
    }

    public function getDestinatario(): ?Usuario
    {
        return $this->destinatario;
    }

    public function setDestinatario(?Usuario $destinatario): static
    {
        $this->destinatario = $destinatario;

        return $this;
    }

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(?string $contenido): static
    {
        $this->contenido = $contenido;

        return $this;
    }

    public function getFechaEnvio(): ?\DateTimeImmutable
    {
        return $this->fechaEnvio;
    }

    public function setFechaEnvio(\DateTimeImmutable $fechaEnvio): static
    {
        $this->fechaEnvio = $fechaEnvio;

        return $this;
    }

    public function isLeido(): bool
    {
        return $this->leido;
    }

    public function setLeido(bool $leido): static
    {
        $this->leido = $leido;

        return $this;
    }

    public function getPalabraCompartida(): ?Palabra
    {
        return $this->palabraCompartida;
    }

    public function setPalabraCompartida(?Palabra $palabraCompartida): static
    {
        $this->palabraCompartida = $palabraCompartida;

        return $this;
    }
}
