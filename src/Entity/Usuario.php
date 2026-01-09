<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
    private int $id;

    #[ORM\Column(type:"string", length:255)]
    private string $nombre;

    #[ORM\Column(type:"string", length:255, unique:true)]
    private string $email;

    #[ORM\Column(type:"string")]
    private string $password;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $fechaRegistro;

    #[ORM\Column(type:"text", nullable:true)]
    private ?string $biografia;

    #[ORM\OneToMany(mappedBy:"usuario", targetEntity:Palabra::class)]
    private Collection $palabras;

    #[ORM\OneToMany(mappedBy:"usuario", targetEntity:Comentario::class)]
    private Collection $comentarios;

    #[ORM\OneToMany(mappedBy:"usuario", targetEntity:Valoracion::class)]
    private Collection $valoraciones;

    #[ORM\OneToMany(mappedBy:"seguidor", targetEntity:Seguimiento::class)]
    private Collection $seguimientosQueHace;

    #[ORM\OneToMany(mappedBy:"seguido", targetEntity:Seguimiento::class)]
    private Collection $seguimientosQueRecibe;

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }


    public function getFechaRegistro(): \DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;
        return $this;
    }

    public function getNombre(): ?string {
    return $this->nombre;
    }

    public function setNombre(string $nombre): self {
        $this->nombre = $nombre;
        return $this;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array { return ['ROLE_USER']; }
    public function eraseCredentials(): void {}
    public function getUserIdentifier(): string { return $this->email; }

    public function __construct() {
        $this->palabras = new ArrayCollection();
        $this->comentarios = new ArrayCollection();
        $this->valoraciones = new ArrayCollection();
        $this->seguimientosQueHace = new ArrayCollection();
        $this->seguimientosQueRecibe = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
