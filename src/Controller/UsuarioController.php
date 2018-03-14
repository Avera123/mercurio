<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UsuarioController extends Controller
{
    /**
     * @Route("/usuario", name="usuario")
     */
    public function index()
    {

        // Get cURL resource
        $curl = curl_init();
// Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => 'http://oro.avera.com/app_dev.php/api/get/casos',
        ));
// Send the request & save response to $resp
        $resp = curl_exec($curl);
// Close request to clear up some resources
        curl_close($curl);


        return $this->render('base.html.twig',array(
            'casos' => $resp
        ));
    }
}
