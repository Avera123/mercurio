<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller{

	/**
	 * @Route("/acceso", name="acceso")
	 */
	public function login(Request $request,AuthenticationUtils $authUtils)
	{
		$error = $authUtils->getLastAuthenticationError();
		$lastUsername = $authUtils->getLastUsername();
		return $this->render('Login/login.html.twig', array(
			'last_username' => $lastUsername,
			'error'         => $error,
		));

	}

	/**
	 * @Route("/logout")
	 */
	public function logoutAction(){
		throw new \RuntimeException('Esta funcion jamas debe ser llamada directamente');
	}
}