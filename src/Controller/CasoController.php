<?php

namespace App\Controller;

use App\Form\ArchivoType;
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

class CasoController extends Controller
{
    /**
     * @Route("/caso/listar", name="casoListar")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'caso/lista/' . $this->getUser()->getCodigoClienteFk(),
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $this->render('Caso/listar.html.twig', array(
            'casos' => $resp
        ));
    }

    /**
     * @Route("/caso/registrar/informacionRespuesta/{codigoCaso}",requirements={"codigoCaso":"\d+"}, name="responderSolucitudInformacion")
     */
    public function registrarSolucion(Request $request, $codigoCaso = null, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        /**
         * @var $arCaso Caso
         */
        $arCaso = $this->getCasoUno($codigoCaso);
        $arCaso = $arCaso[0];
        $user = $this->getUser()->getCodigoUsuarioPk();

//        var_dump($arCaso);
//        var_dump($arCaso['respuestaSolicitudInformacion']);
//        exit();
        $form = $this->createFormBuilder()
            ->add('requisitoInformacion', TextareaType::class, array(
//                'data' => ,
                'attr' => array(
                    'id' => '_requisitoInformacion',
                    'name' => '_requisitoInformacion',
                    'class' => 'form-control',
                ),
                'required' => false
            ))
            ->add('btnEnviar', SubmitType::class, array(
                'attr' => array(
                    'id' => '_btnEnviar',
                    'name' => '_btnEnviar'
                ), 'label' => 'RESPONDER'
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $arEnvia = array(
                "respuestaInformacion" => $form->get('requisitoInformacion')->getData()
            );

            $arrEnviar = json_encode($arEnvia);
            $ch = curl_init($serviceUrl . 'caso/respuesta/informacion/' . $arCaso['codigoCasoPk']);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arrEnviar);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($arrEnviar))
            );

            curl_exec($ch);

            echo "<script>window.opener.location.reload();window.close()</script>";
        }
        return $this->render('Caso/responderSolicitudInformacion.html.twig', [
            'form' => $form->createView(),
            'arCaso' => $arCaso,
            'dataText' => $arCaso['respuestaSolicitudInformacion'] ?? '*',
        ]);
    }

    /**
     * @Route("/caso/detalle/{codigoCaso}", name="casoDetalle")
     */
    public function detalle(Request $request, $codigoCaso)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        //Consultar caso
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'caso/lista/' . $this->getUser()->getCodigoClienteFk() . '/' . $codigoCaso,
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        //Consultar tareas del caso
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'tarea/lista/caso/' . $codigoCaso,
        ));
        $arrTareas = json_decode(curl_exec($curl));
        curl_close($curl);

        //Consultar comentarios del caso
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'comentario/lista/' . $codigoCaso . "/0",
        ));
        $arrComentarios = json_decode(curl_exec($curl));
        curl_close($curl);

        //Consultar archivos del caso
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'archivo/lista/' . 1 . '/' . $codigoCaso,
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
                    $strDestino = "/var/www/archivosoro/1/";
                    $strArchivo = md5(uniqid()) . '.' . $objArchivo->guessExtension();

                    $arrArchivo = array(
                        "nombre" => $objArchivo->getClientOriginalName(),
                        "nombreAlmacenamiento" => $strArchivo,
                        "extension" => $objArchivo->getClientOriginalExtension(),
                        "tamano" => $objArchivo->getClientSize(),
                        "tipo" => $objArchivo->getClientMimeType(),
                        "fecha" => new \DateTime('now'),
                        "directorio" => 1,
                        "codigoDocumento" => 1,
                        "numero" => $codigoCaso
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

                    return $this->redirect($this->generateUrl('casoDetalle', array('codigoCaso' => $codigoCaso)));

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

                $ch = curl_init($serviceUrl . 'comentario/nuevo/caso/' . $codigoCaso . '/' . $this->getUser()->getUserName());
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arrEnviar);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($arrEnviar))
                );
                $result = curl_exec($ch);

                return $this->redirect($this->generateUrl('casoDetalle', array('codigoCaso' => $codigoCaso)));
            }
        }


        return $this->render('Caso/detalle.html.twig', array(
            'form' => $form->createView(),
            'formComentario' => $formComentario->createView(),
            'caso' => $resp,
            'arrTareas' => $arrTareas,
            'arrComentarios' => $arrComentarios,
            'arrArchivos' => $arrArchivos
        ));
    }

    /**
     * @Route("/caso/listar/pendientes", name="casoListarPendientes")
     */
    public function listaPendientes()
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'caso/lista/solucionado/' . $this->getUser()->getCodigoClienteFk() . '/0',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $this->render('Caso/listarPendientes.html.twig', array(
            'casos' => $resp
        ));
    }

    /**
     * @Route("/caso/listar/solucionados", name="casoListarSolucionados")
     */
    public function listaSolucionados()
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'caso/lista/solucionado/' . $this->getUser()->getCodigoClienteFk() . '/1',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $this->render('Caso/listarSolucionados.html.twig', array(
            'casos' => $resp
        ));
    }

    /**
     * @Route("/caso/nuevo/{codigoCaso}", requirements={"codigoCaso":"\d+"}, name="registrarCaso")
     */
    public function nuevo(Request $request, $codigoCaso = null, UserInterface $user)
    {

        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        if ($codigoCaso != null) {
            $arCaso = $this->getCasoUno($codigoCaso);
        } else {
            $arCaso = null;
        }

        $options = array(
            'areas' => $this->listadoAreas(),
            'cargos' => $this->listadoCargos(),
            'prioridades' => $this->listadoPrioridad(),
            'categorias' => $this->listadoCategoriaCasos(),
            'arCaso' => $arCaso,
            'contacto' => $user->getContacto(),
            'correo' => $user->getCorreo(),
            'telefono' => $user->getTelefono(),
            'extension' => $user->getExtension(),
            'codigoCargoFk' => $user->getCodigoCargoFk(),
            'codigoAreaFk' => $user->getCodigoAreaFk());

        $form = $this->createForm(CasoType::class, $options);
        $form->handleRequest($request);

        $res = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            $arCaso = array(
                "asunto" => $res['asunto'],
                "correo" => $res['correo'],
                "contacto" => $res['contacto'],
                "telefono" => $res['telefono'],
                "extension" => $res['extension'],
                "descripcion" => $res['descripcion'],
                "codigo_categoria_caso_fk" => $res['categoria']->codigoCategoriaCasoPk,
                "codigo_prioridad_fk" => $res['prioridad']->codigo_prioridad_pk,
                "codigo_cliente_fk" => $user->getCodigoClienteFk(),
                "codigo_area_fk" => $res['area']->codigoAreaPk,
                "codigo_cargo_fk" => $res['cargo']->codigoCargoPk
            );

            $arrEnviar = json_encode($arCaso);

            if (isset($res['arCaso'][0]->codigoCasoPk)) {
                $ch = curl_init($serviceUrl . 'caso/nuevo/' . $res['arCaso'][0]->codigoCasoPk);
            } else {
                $ch = curl_init($serviceUrl . 'caso/nuevo');
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arrEnviar);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($arrEnviar))
            );

            $result = curl_exec($ch);

            return $this->redirect($this->generateUrl('casoListar'));

        }

        return $this->render('Caso/nuevo.html.twig',
            array(
                'form' => $form->createView(),
                'arCaso' => $arCaso
            ));
    }

    public function listadoAreas()
    {
        // Get cURL resource
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => $serviceUrl . 'area/lista',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function listadoCargos()
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => $serviceUrl . 'cargo/lista',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function listadoPrioridad()
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => $serviceUrl . 'prioridad/lista',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function listadoCategoriaCasos()
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => $serviceUrl . 'caso/lista/categoria',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function getCasoUno($codigoCaso = null)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        if ($codigoCaso != null) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $serviceUrl . 'caso/lista/' . $this->getUser()->getCodigoClienteFk() . '/' . $codigoCaso,
            ));
            $resp = json_decode(curl_exec($curl), true);
            curl_close($curl);
        } else {
            $resp = false;
        }

        return $resp;
    }

    /**
     * @Route("/caso/reabrir/{codigoCaso}", requirements={"codigoCaso":"\d+"}, name="reabrirCaso")
     */
    public function reabrirCaso($codigoCaso = null)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        if ($codigoCaso != null) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $serviceUrl . 'caso/reabrir/' . $codigoCaso,
            ));
            $resp2 = json_decode(curl_exec($curl));
            curl_close($curl);
        } else {
            $resp2 = false;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'caso/lista/solucionado/' . $this->getUser()->getCodigoClienteFk() . '/1',
        ));

        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $this->render('Caso/listarSolucionados.html.twig', array(
            'casos' => $resp
        ));
    }

    /**
     * @Route("/caso/borrar/{codigoCaso}", requirements={"codigoCaso":"\d+"}, name="borrarCaso")
     */
    public function borrarCaso($codigoCaso = null)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        if ($codigoCaso != null) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $serviceUrl . 'caso/borra/' . $codigoCaso,
            ));
            $resp2 = json_decode(curl_exec($curl));
            curl_close($curl);
        } else {
            $resp2 = false;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'caso/lista/solucionado/' . $this->getUser()->getCodigoClienteFk() . '/0',
        ));

        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $this->render('Caso/listarPendientes.html.twig', array(
            'casos' => $resp
        ));
    }
}
