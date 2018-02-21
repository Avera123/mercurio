<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class CasoController extends Controller
{
    /**
     * @Route("/caso/listar", name="casoListar")
     */
    public function index()
    {
        // Get cURL resource
        $curl = curl_init();
// Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => 'http://soga.oro.com/app_dev.php/api/lista/casos/1',
        ));
// Send the request & save response to $resp
        $resp = curl_exec($curl);
// Close request to clear up some resources
        curl_close($curl);

        $respuesta = json_decode($resp);
// replace this line with your own code!
        return $this->render('Caso/listar.html.twig', array(
            'casos' => $respuesta
        ));
    }
}
