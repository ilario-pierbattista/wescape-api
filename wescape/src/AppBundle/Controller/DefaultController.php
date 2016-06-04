<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/homepage.html.twig');
    }

    /**
     * @Route("/qr-generator", name="qr_generator")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function codeGeneratorAction(Request $request) {
        return $this->render('default/qr-generator.html.twig');
    }
}
