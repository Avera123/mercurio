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
        // Get cURL resource


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_URL => 'https://my-json-server.typicode.com/Avera123/jsonserver/usuarios',
            CURLOPT_URL => 'http://oro.avera.com/app_dev.php/api/lista/casos/'.$user->getCodigoClienteFk(),
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
    public function nuevo(Request $request, $codigoCaso = null) {
        $em = $this->getDoctrine()->getManager(); // instancia el entity manager
//      $user = $this->getUser(); // trae el usuario actual
//        $arCaso = new Caso(); //instance class

//        if($codigoCaso) {
//            $arCaso = $em->getRepository('AppBundle:Caso')->find($codigoCaso);
//        } else {
//            $arCaso->setEstadoAtendido(false);
//            $arCaso->setEstadoSolucionado(false);
//        }

        $form = $this->createForm(CasoType::class); //create form
        $form->handleRequest($request);

        $res = json_encode($form->getData());

        if ($form->isSubmitted() && $form->isValid()) {
//            $arCaso->setCodigoUsuarioAtiendeFk($user->getCodigoUsuarioPk());
//            if(!$codigoCaso) {
//                $arCaso->setFechaRegistro(new \DateTime('now'));
//            }
//            $em->persist($arCaso);
//            $em->flush();
            return $this->redirect($this->generateUrl('casoListar'));
        }

        return $this->render('Caso/nuevo.html.twig',
            array(
                'form' => $form->createView(),
            ));
    }
}
