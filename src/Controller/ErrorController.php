<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ErrorController extends Controller
{
    /**
     * @Route("/error/listar", name="error_listar")
     */
    public function errorLista()
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'error/lista/1/'. $this->getUser()->getCodigoClienteFk(),
        ));
        $arrErrores = json_decode(curl_exec($curl));
        $arrErrorLista = $arrErrores->registros;
        curl_close($curl);

        return $this->render('Error/lista.html.twig', array(
            'errores' => $arrErrorLista
        ));
    }
}
