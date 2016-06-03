<?php

namespace Wescape\ApiBundle\Controller;


use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Voryx\RESTGeneratorBundle\Controller\VoryxController;
use FOS\RestBundle\View\View as FOSView;

/**
 * Class EmergencyController
 *
 * @package Wescape\ApiBundle\Controller
 *
 * @RouteResource("emergency")
 */
class EmergencyController extends VoryxController
{
    /**
     * Get a User entity
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     * @ApiDoc(
     *     resource=true,
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     200="Returned if the notification are sent",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some error occurs"}
     * )
     * @Security(
     * "has_role('ROLE_ADMIN')"
     * )
     */
    public function getAction() {
        try {
            $emergencyDipatcher = $this->get('core.emergency_dispatcher');
            $report = $emergencyDipatcher->notifyAll();
            return FOSView::create([
                "successful_sent" => $report['successes'],
                "failures" => $report['failures']
            ]);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}