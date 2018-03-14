<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GeneralController extends Controller
{
    /**
     * @Route("/archivo/descargar/{codigoArchivo}", name="archivo_descarga")
     */
    public function descargar($codigoArchivo)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'archivo/descargar/' . $codigoArchivo,
        ));
        $arrArchivo = json_decode(curl_exec($curl));
        $arrArchivo = $arrArchivo[0];
        curl_close($curl);

        $strRuta = "/var/www/archivosoro/1/" . $arrArchivo->nombreAlmacenamiento;
        // Generate response
        $response = new Response();
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', $arrArchivo->tipo);
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $arrArchivo->nombre . '";');
        $response->headers->set('Content-length', $arrArchivo->tamano);
        $response->sendHeaders();
        if(file_exists ($strRuta)){
            $response->setContent(readfile($strRuta));
        }else{
            echo "<script>alert('No existe el archivo en el servidor a pesar de estar asociado en base de datos, por favor comuniquese con soporte');window.close()</script>";
        }
        return $response;
    }
}
