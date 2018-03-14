<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
//use Symfony\Component\Form\Form;
use App\Form\CasoType;
use App\Entity\Configuracion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PruebaController extends Controller
{
    /**
     * @Route("/prueba", name="prueba")
     */
    public function index(UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl.'lista/casos/'.$user->getCodigoClienteFk(),
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return new Response(
            "<html><body>Conectado al Servicio {$serviceUrl}</body></html>"
        );
    }
}
