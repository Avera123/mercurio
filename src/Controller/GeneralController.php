<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GeneralController extends Controller
{
    /**
     * @Route("/archivo/descargar/{tipo}/{tamano}/{nombre}", requirements={"tipo":"[a-zA-Z0-9./]+","tamano":"\d+","nombre":"[a-zA-Z0-9. ]+"}, name="archivo_descarga")
     */
    public function descargar($tipo = null, $tamano = 0, $nombre = null)
    {
//        $em = $this->getDoctrine()->getManager();
//        $arArchivo = $em->getRepository('App:Archivo')->find($numero);
//        $strRuta = $arArchivo->getDirectorioRel()->getRutaPrincipal() . $arArchivo->getDirectorioRel()->getNumero() . "/" . $arArchivo->getCodigoArchivoPk() . "_" . $arArchivo->getNombre();
        $strRuta = '/var/www/archivosoro/';
        // Generate response
        $response = new Response();
//        $curl = curl_init();
//        curl_setopt_array($curl, array(
//            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'caso/lista/' . $this->getUser()->getCodigoClienteFk(),
//        ));
//        $resp = json_decode(curl_exec($curl));
//        curl_close($curl);
        // Set headers
        $response->headers->set('Cache-Control', 'private');
        #$response->headers->set('Content-Type', $tipo);
        $response->headers->set("Content-Type", "image/jpeg");
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $nombre . '";');
        $response->headers->set('Content-Length', $tamano);
//        $response->setContent(readfile($strRuta));
//        var_dump($tipo);
//        exit();
        if(file_exists ($strRuta)){
            $response->setContent(file_get_contents($strRuta));
        }else{
            echo "<script>alert('No existe el archivo en el servidor a pesar de estar asociado en base de datos, por favor comuniquese con soporte');window.close()</script>";
        }
        $response->sendHeaders();
        return $response;
    }
}
