<?php
namespace App\Controller;

use PhpParser\Node\Expr\Exit_;
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
use App\Entity\Configuracion;


class ImplementacionController extends Controller
{

    /**
     * @Route("/implementacion/listar", name="implementacionListar")
     */
    public function implementacionLista()
    {
        $em = $this->getDoctrine()->getManager();
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'implementacion/lista/' . $this->getUser()->getCodigoClienteFk(),
        ));
        $resp = json_decode(curl_exec($curl), true);
        curl_close($curl);


        $result = "";
        if (!isset($resp)) {
            $result = "No hay conexión";
        } elseif (isset($resp['error'])) {
            $result = "Error en la ruta";
        }


        return $this->render('Implementacion/listar.html.twig', array(
            'implementaciones' => $resp,
            'mensaje' => $result
        ));

    }

    /**
     * @Route("/implementacion/detalle/{codigoImplementacion}", name="implementacionDetalle")
     */
    public function implementacionDetalle($codigoImplementacion)
    {
        $em = $this->getDoctrine()->getManager();
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'implementacion/detalle/' . $codigoImplementacion,
        ));

        $curl2 = curl_init();
        curl_setopt_array($curl2, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'implementacion/cabezera/' . $codigoImplementacion,
        ));

        $resp = json_decode(curl_exec($curl));
        $resp2 = json_decode(curl_exec($curl2));

        curl_close($curl);
        curl_close($curl2);

        return $this->render('Implementacion/detalle.html.twig', array(
            'implementacionDetalles' => $resp,
            'implementacion' => $resp2
        ));

    }

    /**
     * @Route("/implementacion/detalle/adjunto/{codigoImplementacionDetalle}", name="implementacionDetalleAdjunto")
     */
    public function adjuntarArchivoImplementacion(Request $request, $codigoImplementacionDetalle)
    {
        $em = $this->getDoctrine()->getManager();
        $serviceUrl = $em->getRepository('App:Configuracion')->getUrl();

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $serviceUrl . 'archivo/lista/' . 3 . '/' . $codigoImplementacionDetalle,
        ));
        $arrArchivos = json_decode(curl_exec($curl));
        curl_close($curl);

        $form = $this->createFormBuilder()
            ->add('archivo', FileType::class)
            ->add('btnGuardar', SubmitType::class, array('label' => 'Cargar'))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('btnGuardar')->isClicked()) {
                $objArchivo = $form['archivo']->getData();
                if ($objArchivo->getClientSize()) {
                    $strDestino = "/almacenamiento/archivosoro/3/";
                    $strArchivo = md5(uniqid()) . '.' . $objArchivo->guessExtension();

                    $arrArchivo = array(
                        "nombre" => $objArchivo->getClientOriginalName(),
                        "nombreAlmacenamiento" => $strArchivo,
                        "extension" => $objArchivo->getClientOriginalExtension(),
                        "tamano" => $objArchivo->getClientSize(),
                        "tipo" => $objArchivo->getClientMimeType(),
                        "fecha" => new \DateTime('now'),
                        "directorio" => 3,
                        "codigoDocumento" => 3,
                        "numero" => $codigoImplementacionDetalle
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

                    return $this->redirect($this->generateUrl('implementacionDetalleAdjunto', array('codigoImplementacionDetalle' => $codigoImplementacionDetalle)));

                } else {
                    echo "El archivo tiene un tamaño mayor al permitido";
                }
            }
        }

        return $this->render("Implementacion/detalleAdjunto.html.twig", [
            'arArchivos' => $arrArchivos,
            'form' => $form->createView()
        ]);
    }


}


?>
