<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
            CURLOPT_URL => $serviceUrl.'caso/lista/'.$this->getUser()->getCodigoClienteFk(),
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
    public function casoDetalle($codigoCaso)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl.'caso/lista/'.$this->getUser()->getCodigoClienteFk().'/'.$codigoCaso,
        ));

        $resp = json_decode(curl_exec($curl));

        curl_close($curl);

        return $this->render('Caso/detalle.html.twig', array(
            'caso' => $resp
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
            CURLOPT_URL => $serviceUrl.'caso/lista/solucionado/'.$this->getUser()->getCodigoClienteFk().'/0',
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
            CURLOPT_URL => $serviceUrl.'caso/lista/solucionado/'.$this->getUser()->getCodigoClienteFk().'/1',
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
    public function nuevo(Request $request, $codigoCaso = null, UserInterface $user) {

        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        if($codigoCaso){
            $arCaso = $this->getCasoUno($codigoCaso);
        }else{
            $arCaso = null;
        }

        $options = array('areas'=>$this->listadoAreas(),'cargos'=> $this->listadoCargos(),'prioridades'=>$this->listadoPrioridad(),'categorias'=>$this->listadoCategoriaCasos(),'arCaso'=> $arCaso);

        $form = $this->createForm(CasoType::class, $options); //create form
//        $form->setData('categoria',$arCaso[0]->categoria);
//        $form->get('categoria')->setData($arCaso[0]->categoria);
        $form->handleRequest($request);

        $res = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {

            $arCaso = array(
                "asunto"=>$res['asunto'],
                "correo"=>$res['correo'],
                "contacto"=>$res['contacto'],
                "telefono"=>$res['telefono'],
                "extension"=>$res['extension'],
                "descripcion"=>$res['descripcion'],
                "codigo_categoria_caso_fk"=>$res['categoria']->codigoCategoriaCasoPk,
                "codigo_prioridad_fk"=>$res['prioridad']->codigo_prioridad_pk,
                "codigo_cliente_fk"=>$user->getCodigoClienteFk(),
                "codigo_area_fk"=>$res['area']->codigoAreaPk,
                "codigo_cargo_fk"=>$res['cargo']->codigoCargoPk,
            ); //instance class

            $arrEnviar =json_encode($arCaso);

            $ch = curl_init($serviceUrl.'caso/nuevo');
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
            CURLOPT_URL => $serviceUrl.'area/lista',
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
            CURLOPT_URL => $serviceUrl.'cargo/lista',
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
            CURLOPT_URL => $serviceUrl.'prioridad/lista',
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
            CURLOPT_URL => $serviceUrl.'caso/lista/categoria',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function getCasoUno($codigoCaso = null)
    {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => $serviceUrl.'caso/lista/'.$this->getUser()->getCodigoClienteFk().'/'.$codigoCaso,
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }
}
