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

class CasoController extends Controller
{
    /**
     * @Route("/caso/listar", name="casoListar")
     */
    public function index(UserInterface $user)
    {
        $arConfiguracion = $this->getUrl();

        $serviceUrl = $arConfiguracion->getServiceUrl();
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => $serviceUrl.'lista/casos/'.$user->getCodigoClienteFk(),
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $this->render('Caso/listar.html.twig', array(
            'casos' => $resp
        ));
    }

    /**
     * @Route("/caso/nuevo/{codigoCaso}", requirements={"codigoCaso":"\d+"}, name="registrarCaso")
     */
    public function nuevo(Request $request, $codigoCaso = null, UserInterface $user) {

        $options = array('areas'=>$this->listadoAreas(),'cargos'=> $this->listadoCargos(),'prioridades'=>$this->listadoPrioridad(),'categorias'=>$this->listadoCategoriaCasos());

        $form = $this->createForm(CasoType::class, $options); //create form
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
                "codigo_categoria_caso_fk"=>$res['categoria']->codigo_categoria_caso_pk,
                "codigo_prioridad_fk"=>$res['prioridad']->codigo_prioridad_pk,
                "codigo_cliente_fk"=>$user->getCodigoClienteFk(),
                "codigo_area_fk"=>$res['area']->codigo_area_pk,
                "codigo_cargo_fk"=>$res['cargo']->codigo_cargo_pk,
            ); //instance class

            $arrEnviar =json_encode($arCaso);

            $ch = curl_init(self::$url.'nuevo/casos');
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
            ));
    }

    public function listadoAreas()
    {
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => self::$url.'lista/area',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function listadoCargos()
    {
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => self::$url.'lista/cargo',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function listadoPrioridad()
    {
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => self::$url.'lista/prioridad',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function listadoCategoriaCasos()
    {
        // Get cURL resource
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => self::$url.'lista/caso/categoria',
        ));
        $resp = json_decode(curl_exec($curl));
        curl_close($curl);

        return $resp;
    }

    public function getUrl(){
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
        $arConfiguracion = new Configuracion();

        $arConfiguracion = $em->getRepository('App:Configuracion')->find(1);

        return $arConfiguracion;
    }

}
