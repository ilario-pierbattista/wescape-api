<?php

namespace Wescape\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;

/**
 * Class DefaultRestController
 *
 * @package AppBundle\Controller
 */
class DefaultRestController extends FOSRestController
{
    public function getIndexAction() {
        $view = $this->view(['text' => 'hello'], 200);
        return $this->handleView($view);
    }
}