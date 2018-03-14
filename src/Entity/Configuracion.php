<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConfiguracionRepository")
 */
class Configuracion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @ORM\Column(type="string")
     */
    private $serviceUrl;

    /**
     * @return mixed
     */
    public function getServiceUrl()
    {
        return $this->serviceUrl;
    }

    /**
     * @param mixed $serviceUrl
     */
    public function setServiceUrl($serviceUrl): void
    {
        $this->serviceUrl = $serviceUrl;
    }

}
