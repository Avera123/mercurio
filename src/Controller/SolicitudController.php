<?php

namespace App\Controller;

use App\Form\SolicitudType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class SolicitudController extends Controller
{
    /**
     * @Route("/solicitud/listar", name="solicitudListar")
     */
    public function index(Request $request)
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

        $form = $this->createFormBuilder()
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->has('arSolicitudAprobar')) {
                $codigoSolicitud = $request->request->get('arSolicitudAprobar');
                //Consultar solicitud
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => $serviceUrl . 'solicitud/aprobar/' . $codigoSolicitud,
                ));
                $resp = json_decode(curl_exec($curl));
                curl_close($curl);
                return $this->redirectToRoute("solicitudListar");
            }
        }


        return $this->render('Solicitud/listar.html.twig', array(
            'solicitudes' => $resp,
            'form' => $form->createView()
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
                "nombre" => $res['nombre'],
                "contacto" => $res['contacto'],
                "telefono" => $res['telefono'],
                "extension" => $res['extension'],
                "correo" => $res['correo'],
                "horas" => $res['horas']->format("H:i:s"),
                "codigo_cliente_fk" => $user->getCodigoClienteFk(),
            );

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

    /**
     * @Route("/solicitud/detalle/{codigoSolicitud}", name="solicitudDetalle")
     */
    public function detalle(Request $request, $codigoSolicitud)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        //Consultar solicitud
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'solicitud/' . $this->getUser()->getCodigoClienteFk() . '/' . $codigoSolicitud,
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        //Consultar comentarios del solicitud
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'comentario/lista/0/' . $codigoSolicitud,
        ));
        $arrComentarios = json_decode(curl_exec($curl));
        curl_close($curl);

        //Consultar archivos del solicitud
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'archivo/lista/' . 2 . '/' . $codigoSolicitud,
        ));
        $arrArchivos = json_decode(curl_exec($curl));
        curl_close($curl);

        $form = $this->createFormBuilder()
            ->add('archivo', fileType::class)
            ->add('btnGuardar', SubmitType::class, array('label' => 'Cargar'))
            ->getForm();
        $form->handleRequest($request);

        $formComentario = $this->createFormBuilder()
            ->add('txtComentario', TextType::class)
            ->add('btnAgregarComentario', SubmitType::class, array('label' => 'Enviar'))
            ->getForm();
        $formComentario->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$objArchivo = $formAdjuntar['adjunto']->getData();
            if ($form->get('btnGuardar')->isClicked()) {
                $objArchivo = $form['archivo']->getData();
                if ($objArchivo->getClientSize()) {
                    $strDestino = "/var/www/archivosoro/2/";
                    $strArchivo = md5(uniqid()) . '.' . $objArchivo->guessExtension();

                    $arrArchivo = array(
                        "nombre" => $objArchivo->getClientOriginalName(),
                        "nombreAlmacenamiento" => $strArchivo,
                        "extension" => $objArchivo->getClientOriginalExtension(),
                        "tamano" => $objArchivo->getClientSize(),
                        "tipo" => $objArchivo->getClientMimeType(),
                        "fecha" => new \DateTime('now'),
                        "directorio" => 2,
                        "codigoDocumento" => 2,
                        "numero" => $codigoSolicitud
                    );

                    $arrEnviar = json_encode($arrArchivo);
                    $ch = curl_init($serviceUrl . 'archivo/nuevo/');
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $arrEnviar);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($arrEnviar))
                    );
                    $result = curl_exec($ch);
                    $form['archivo']->getData()->move($strDestino, $strArchivo);

                    return $this->redirect($this->generateUrl('solicitudDetalle', array('codigoSolicitud' => $codigoSolicitud)));

                } else {
                    echo "El archivo tiene un tamaño mayor al permitido";
                }
            }
        }

        if ($formComentario->isSubmitted() && $formComentario->isValid()) {
            if ($formComentario->get('btnAgregarComentario')->isClicked()) {
                $comentario = $formComentario['txtComentario']->getData();

                $arrComentario = array(
                    "comentario" => $comentario
                );

                $arrEnviar = json_encode($arrComentario);

                $ch = curl_init($serviceUrl . 'comentario/nuevo/solicitud/' . $codigoSolicitud . '/' . $this->getUser()->getUserName());
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arrEnviar);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($arrEnviar))
                );
                $result = curl_exec($ch);

                return $this->redirect($this->generateUrl('solicitudDetalle', array('codigoSolicitud' => $codigoSolicitud)));
            }
        }

        return $this->render('Solicitud/detalle.html.twig', array(
            'form' => $form->createView(),
            'formComentario' => $formComentario->createView(),
            'solicitud' => $resp,
            'arrComentarios' => $arrComentarios,
            'arrArchivos' => $arrArchivos
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
