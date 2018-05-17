<?php

namespace App\Controller;

use App\Form\ArchivoType;
use App\Form\SolicitudType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
//use Symfony\Component\Form\Form;
use App\Form\CasoType;
use App\Entity\Configuracion;

class SolicitudController extends Controller
{
    /**
     * @Route("/solicitud/listar", name="solicitudListar")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'solicitud/lista/' . $this->getUser()->getCodigoClienteFk(),
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $this->render('Solicitud/listar.html.twig', array(
            'solicitudes' => $resp
        ));
    }

    /**
     * @Route("/solicitud/nuevo/{codigoSolicitud}", requirements={"codigoSolicitud":"\d+"}, name="registrarSolicitud")
     */
    public function nuevo(Request $request, $codigoSolicitud = null, UserInterface $user)
    {

        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        if ($codigoSolicitud != null) {
            $arSolicitud = $this->getSolicitudUno($codigoSolicitud);
        } else {
            $arSolicitud = null;
        }

        $options = array('solicitudesTipo' => $this->listadoSolicitudesTipo(), 'arSolicitud' => $arSolicitud);

        $form = $this->createForm(SolicitudType::class, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $res = $form->getData();
            $arSolicitud = array(
                "codigo_solicitud_tipo_fk" => $res['solicitudTipoRel']->codigoSolicitudTipoPk,
                "descripcion" => $res['descripcion'],
                "codigo_cliente_fk" => $user->getCodigoClienteFk(),
            );
            dump($arSolicitud);

            $arrEnviar = json_encode($arSolicitud);

            if (isset($res['arSolicitud'][0]['codigoSolicitudPk'])) {
                $ch = curl_init($serviceUrl . 'solicitud/nuevo/' . $res['arSolicitud'][0]['codigoSolicitudPk']);
            } else {
                $ch = curl_init($serviceUrl . 'solicitud/nuevo/0');
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arrEnviar);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($arrEnviar))
            );

            $result = curl_exec($ch);

            return $this->redirect($this->generateUrl('solicitudListar'));

        }

        return $this->render('Solicitud/nuevo.html.twig',
            array(
                'form' => $form->createView(),
                'arSolicitud' => $arSolicitud
            ));
    }

    public function getSolicitudUno($codigoSolicitud = null)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        if ($codigoSolicitud != null) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $serviceUrl . 'solicitud/' . $this->getUser()->getCodigoClienteFk() . '/' . $codigoSolicitud,
            ));
            $resp = json_decode(curl_exec($curl), true);
            curl_close($curl);
        } else {
            $resp = false;
        }

        return $resp;
    }

    public function listadoSolicitudesTipo()
    {
        // Get cURL resource
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => $serviceUrl . 'solicitud/tipo/lista',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

}
