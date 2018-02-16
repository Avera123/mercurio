<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioRepository")
 */
class Usuario
{
    /**
     * @ORM\codigo
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $codigo;

    /**
     * @ORM\nombre
     * @ORM\Column(type="string", length="500", nullable=true)
     */
    private $nombre;

    /**
     * @ORM\rol
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $rol;

}
