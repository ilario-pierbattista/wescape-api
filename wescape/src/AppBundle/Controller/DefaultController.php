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
        $em = $this->getDoctrine()->getEntityManager();
        /** @var Node $node */
        $node = $em->getRepository("CoreBundle:Node")->findOneBy(['id' => 1]);
        $form = $this->createForm(QRCodeType::class);
        
        $form->handleRequest($request);
        $config = [
            'message' => NULL,
            'size' => 200,
            'padding' => 10,
            'extension' => 'png'
        ];
        
        if ($form->isSubmitted() && $form->isValid()) {
            $node = $form->get('node')->getData();
            $config['size'] = $form->get('size')->getData();
            $config['extension'] = $form->get('extension')->getData();
            $config['padding'] = $form->get('padding')->getData();
            $config['message'] = $node->getId() . "_" . base64_encode(random_bytes(6));
        }
        
        return $this->render('default/qr-generator.html.twig', [
            'form'    => $form->createView(),
            'node'    => $node,
            'config' => $config,
        ]);
    }
}
