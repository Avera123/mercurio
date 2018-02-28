<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CommitsController extends Controller
{
    /**
     * @Route("/commit/soga", name="commitSoga")
     */
    public function index()
    {
        $serviceUrl = "https://api.github.com/repos/SogaApp/vanadio/commits";//url listado de commits
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: SogaApp',
        ));

        $resp = json_decode(curl_exec($ch));
        curl_close($ch);
        return $this->render('Commit/listar.html.twig', array(
            'commits' => $resp
        ));
    }

    /**
     * @Route("/commits/detalle/{codigoCommit}", name="commitDetalle")
     */
    public function commitDetalle($codigoCommit)
    {
        $serviceUrl = "https://api.github.com/repos/SogaApp/vanadio/commits/{$codigoCommit}";//url detalle del commit
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serviceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: SogaApp',
        ));

        $resp = json_decode(curl_exec($ch));
        curl_close($ch);
        return $this->render('Commit/detalle.html.twig', array(
            'commit' => $resp
        ));
    }
}
