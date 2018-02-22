<?php

namespace App\Entity;



use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="usuario")
 */
class Usuario implements UserInterface, \Serializable
{


	/**
	 * @ORM\Column(name="codigo_usuario_pk",type="string")
	 * @ORM\Id
	 */
	private $codigoUsuarioPk;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $correo;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $nombres;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $apellidos;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $clave;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $verificado;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $token;

	/**
	 * @ORM\Column(name="codigo_cliente_fk", type="integer", nullable=true)
	 */
	private $codigoClienteFk;

	/**
	 * Se implementan métodos de la clase User del core de Symfony además de los metodos de la entidad própia.
	 *
	 */
	public function serialize()
	{
		return serialize(array(
			$this->codigoUsuarioPk,
			$this->clave
		));
	}

	public function unserialize($serialized)
	{
		list(
			$this->codigoUsuarioPk,
			$this->clave

			) = unserialize($serialized);
	}

	public function getUsername()
	{
		return $this->getCodigoUsuarioPk();
	}

	public function getRoles()
	{

		return array('ROLE_USER');
	}

	public function getPassword()
	{
		return $this->getClave();
	}

	public function getSalt()
	{
		return null;
	}

	public function eraseCredentials()
	{

	}
	/**
	 * end métodos de la clase User del core.
	 */


	/**
	 * Set codigoUsuarioPk
	 *
	 * @param string $codigoUsuarioPk
	 *
	 * @return Usuario
	 */
	public function setCodigoUsuarioPk($codigoUsuarioPk)
	{
		$this->codigoUsuarioPk = $codigoUsuarioPk;

		return $this;
	}

	/**
	 * Get codigoUsuarioPk
	 *
	 * @return string
	 */
	public function getCodigoUsuarioPk()
	{
		return $this->codigoUsuarioPk;
	}

	/**
	 * Set correo
	 *
	 * @param string $correo
	 *
	 * @return Usuario
	 */
	public function setCorreo($correo)
	{
		$this->correo = $correo;

		return $this;
	}

	/**
	 * Get correo
	 *
	 * @return string
	 */
	public function getCorreo()
	{
		return $this->correo;
	}

	/**
	 * Set nombres
	 *
	 * @param string $nombres
	 *
	 * @return Usuario
	 */
	public function setNombres($nombres)
	{
		$this->nombres = $nombres;

		return $this;
	}

	/**
	 * Get nombres
	 *
	 * @return string
	 */
	public function getNombres()
	{
		return $this->nombres;
	}

	/**
	 * Set apellidos
	 *
	 * @param string $apellidos
	 *
	 * @return Usuario
	 */
	public function setApellidos($apellidos)
	{
		$this->apellidos = $apellidos;

		return $this;
	}

	/**
	 * Get apellidos
	 *
	 * @return string
	 */
	public function getApellidos()
	{
		return $this->apellidos;
	}

	/**
	 * Set clave
	 *
	 * @param string $clave
	 *
	 * @return Usuario
	 */
	public function setClave($clave)
	{
		$this->clave = $clave;

		return $this;
	}

	/**
	 * Get clave
	 *
	 * @return string
	 */
	public function getClave()
	{
		return $this->clave;
	}

	/**
	 * Set verificado
	 *
	 * @param boolean $verificado
	 *
	 * @return Usuario
	 */
	public function setVerificado($verificado)
	{
		$this->verificado = $verificado;

		return $this;
	}

	/**
	 * Get verificado
	 *
	 * @return boolean
	 */
	public function getVerificado()
	{
		return $this->verificado;
	}

	/**
	 * Set token
	 *
	 * @param string $token
	 *
	 * @return Usuario
	 */
	public function setToken($token)
	{
		$this->token = $token;

		return $this;
	}

	/**
	 * Get token
	 *
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * Set codigoClienteFk
	 *
	 * @param integer $codigoClienteFk
	 *
	 * @return Usuario
	 */
	public function setCodigoClienteFk($codigoClienteFk)
	{
		$this->codigoClienteFk = $codigoClienteFk;

		return $this;
	}

	/**
	 * Get codigoClienteFk
	 *
	 * @return integer
	 */
	public function getCodigoClienteFk()
	{
		return $this->codigoClienteFk;
	}




}
