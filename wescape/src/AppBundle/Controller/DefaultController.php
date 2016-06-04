<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wescape\CoreBundle\Entity\Node;
use Wescape\CoreBundle\Form\QRCodeType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
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
        //$em = $this->getDoctrine()->getEntityManager();
        /** @var Node $node */
        //$nodo = $em->getRepository("CoreBundle:Node")->findOneBy(['id' => 1]);
        $form = $this->createForm(QRCodeType::class);

        $form->handleRequest($request);
        $message = NULL;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Node $node */
            $node = $form->get('node')->getData();
            $message = $node->getId() . "_" . base64_encode(random_bytes(6));
        }

        return $this->render('default/qr-generator.html.twig', [
            'form'      => $form->createView(),
            'message' => $message,
            'node_name' => ""
        ]);
    }
}
