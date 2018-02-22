<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class CasoController extends Controller
{
    /**
     * @Route("/caso/listar", name="casoListar")
     */
    public function index(UserInterface $user)
    {
        // Get cURL resource


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => 'http://oro.juan.com/app_dev.php/api/lista/casos/'.$user->getCodigoClienteFk(),
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        $respuesta = json_decode($resp);

        return $this->render('Caso/listar.html.twig', array(
            'casos' => $respuesta
        ));
    }

    /**
     * @Route("/caso/nuevo/{codigoCaso}", requirements={"codigoCaso":"\d+"}, name="registrarCaso")
     */
    public function nuevo(Request $request, $codigoCaso = null) {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
//      $user = $this->getUser(); // trae el usuario actual
        $arCaso = new Caso(); //instance class

//        if($codigoCaso) {
//            $arCaso = $em->getRepository('AppBundle:Caso')->find($codigoCaso);
//        } else {
//            $arCaso->setEstadoAtendido(false);
//            $arCaso->setEstadoSolucionado(false);
//        }

        $form = $this->createForm(FormTypeCaso::class, $arCaso); //create form
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            $arCaso->setCodigoUsuarioAtiendeFk($user->getCodigoUsuarioPk());
            if(!$codigoCaso) {
                $arCaso->setFechaRegistro(new \DateTime('now'));
            }
            $em->persist($arCaso);
            $em->flush();
            return $this->redirect($this->generateUrl('listadoCasos'));
        }

        return $this->render('AppBundle:Caso:nuevo.html.twig',
            array(
                'form' => $form->createView(),
            ));
    }
}
