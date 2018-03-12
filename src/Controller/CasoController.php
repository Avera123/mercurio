<?php

namespace App\Controller;

use App\Form\ArchivoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * @Route("/caso/detalle/{codigoCaso}", name="casoDetalle")
     */
    public function detalle(Request $request, $codigoCaso)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'caso/lista/' . $this->getUser()->getCodigoClienteFk() . '/' . $codigoCaso,
        ));

        $resp = json_decode(curl_exec($curl));

        curl_close($curl);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://192.168.15.97/oro/public/index.php/api/tarea/lista/caso/' .  $codigoCaso,
        ));

        $arrTareas = json_decode(curl_exec($curl));

        curl_close($curl);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://192.168.15.97/oro/public/index.php/api/comentario/lista/caso/' .  $codigoCaso,
        ));

        $arrComentarios = json_decode(curl_exec($curl));

        curl_close($curl);

        //$formAdjuntar = $this->createForm(ArchivoType::class);
        //$formAdjuntar->handleRequest($request);
        $form = $this->createFormBuilder()
            ->add('archivo', fileType::class)
            ->add('btnGuardar', SubmitType::class, array('label'  => 'Cargar'))
            ->getForm();
        $form->handleRequest($request);

        //$adjunto = $formAdjuntar->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            //$objArchivo = $formAdjuntar['adjunto']->getData();
            if($form->get('btnGuardar')->isClicked()) {
                $objArchivo = $form['archivo']->getData();
                if ($objArchivo->getClientSize()){
                    $strDestino = "/var/www/archivosoro/";
                    $strArchivo = md5(uniqid()).'.'.$objArchivo->guessExtension();

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

                    //return $this->redirect($this->generateUrl('brs_ad_archivos_lista', array('codigoDocumento' => $codigoDocumento, 'numero' => $numero)));
                } else {
                    echo "El archivo tiene un tamaño mayor al permitido";
                }
            }
        }
        

        return $this->render('Caso/detalle.html.twig', array(
            'form' => $form->createView(),
            'caso' => $resp,
            'arrTareas' => $arrTareas,
            'arrComentarios' => $arrComentarios
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

        $options = array('areas' => $this->listadoAreas(), 'cargos' => $this->listadoCargos(), 'prioridades' => $this->listadoPrioridad(), 'categorias' => $this->listadoCategoriaCasos(), 'arCaso' => $arCaso);

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
            $resp = json_decode(curl_exec($curl));
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
