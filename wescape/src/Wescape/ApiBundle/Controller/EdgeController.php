<?php

namespace Wescape\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View as FOSView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Voryx\RESTGeneratorBundle\Controller\VoryxController;
use Wescape\CoreBundle\Entity\Edge;
use Wescape\CoreBundle\Form\EdgeType;

/**
 * Edge controller.
 * @RouteResource("Edge")
 */
class EdgeController extends VoryxController
{
    /**
     * Get a Edge entity
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\EdgeType",
     *     output="Wescape\CoreBundle\Entity\Edge",
     *     statusCodes={
     *     200="Returned if the edge is found",
     *     404="Returned if the edge does not exists"}
     * )
     */
    public function getAction(Edge $edge) {
        return $edge;
    }

    /**
     * Get all Edge entities.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset
     *    from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="0", description="How many
     *                           notes to return.")
     * @QueryParam(name="order_by", nullable=true, array=true, description="Order by
     *                              fields. Must be an array ie.
     *                              &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, array=true, description="Filter by
     *                             fields. Must be an array ie. &filters[id]=3")
     * @ApiDoc(
     *     resource=true,
     *     output="Wescape\CoreBundle\Entity\Edge",
     *     statusCodes={
     *     200="Returned in case of success. Almost always."}
     * )
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher) {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $limit = $limit == 0 ? null : $limit;
            $entities = $em->getRepository('CoreBundle:Edge')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                return $entities;
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a Edge entity.
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\EdgeType",
     *     output="Wescape\CoreBundle\Entity\Edge",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     201="Returned if the edge is created",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function postAction(Request $request) {
        $entity = new Edge();
        $form = $this->createForm(EdgeType::class, $entity, array("method" =>
            $request->getMethod
            ()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $entity;
        }

        return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Update a Edge entity.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param         $edge
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\EdgeType",
     *     output="Wescape\CoreBundle\Entity\Edge",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     200="Returned if the edge is updated",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     404="Returned if the resource has been not found",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function putAction(Request $request, Edge $edge) {
        try  {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(EdgeType::class, $edge, array("method" =>
                $request->getMethod
                ()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();

                return $edge;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partial Update to a Edge entity.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param         $edge
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\EdgeType",
     *     output="Wescape\CoreBundle\Entity\Edge",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     200="Returned if the edge is updated",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function patchAction(Request $request, Edge $edge) {
        return $this->putAction($request, $edge);
    }

    /**
     * Delete a Edge entity.
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param         $edge
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\EdgeType",
     *     output="Wescape\CoreBundle\Entity\Edge",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     204="Returned if the edge is deleted",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Edge $edge) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($edge);
            $em->flush();

            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
