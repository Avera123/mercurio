<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PanelControlController extends Controller
{
    /**
     * @Route("/", name="panel_control")
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

        $respuesta = json_decode($resp);
// replace this line with your own code!
        return $this->render('Pages/PanelDeControl.html.twig', array(
            'casos' => $respuesta
        ));
    }

    /**
     * @Route("/manual", name="panel_manual")
     */
    public function manual(){
        header("Content-disposition: attachment; filename=ManualMercurio.pdf");
        header("Content-type: application/pdf");
        $ds = DIRECTORY_SEPARATOR;
        $manual = realpath(implode($ds, [__DIR__, '..', '..', 'public', 'manuales', 'ManualMercurio.pdf']));
        readfile($manual);
        exit();
    }


}
