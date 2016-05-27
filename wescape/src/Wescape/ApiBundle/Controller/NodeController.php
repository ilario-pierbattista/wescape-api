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
use Wescape\CoreBundle\Entity\Node;
use Wescape\CoreBundle\Form\NodeType;

/**
 * Node controller.
 * @RouteResource("Node")
 */
class NodeController extends VoryxController
{
    /**
     * Get a Node entity
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\NodeType",
     *     output="Wescape\CoreBundle\Entity\Node",
     *     statusCodes={
     *     200="Returned if the node is found",
     *     404="Returned if the node does not exists"}
     * )
     */
    public function getAction(Node $entity) {
        return $entity;
    }

    /**
     * Get all Node entities.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset
     *                            from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="0", description="How many
     *                           notes to return.")
     * @QueryParam(name="order_by", nullable=true, array=true, description="Order by
     *                              fields. Must be an array ie.
     *                              &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, array=true, description="Filter by
     *                             fields. Must be an array ie. &filters[id]=3")
     * @ApiDoc(
     *     resource=true,
     *     output="Wescape\CoreBundle\Entity\Node",
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
            $entities = $em->getRepository('CoreBundle:Node')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                return $entities;
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a Node entity.
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\NodeType",
     *     output="Wescape\CoreBundle\Entity\Node",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     201="Returned if the node is created",
     *     401="Returned if the client is not authorized",
     *     404="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function postAction(Request $request) {
        $entity = new Node();
        $form = $this->createForm(get_class(new NodeType()), $entity, array("method" =>
            $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //return FOSView::create($form->getData(), Codes::HTTP_INTERNAL_SERVER_ERROR);

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $entity;
        }

        return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Update a Node entity.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param         $entity
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\NodeType",
     *     output="Wescape\CoreBundle\Entity\Node",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     200="Returned if the node is updated",
     *     401="Returned if the client is not authorized",
     *     404="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function putAction(Request $request, Node $entity) {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(get_class(new NodeType()), $entity, array("method" =>
                $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();

                return $entity;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partial Update to a Node entity.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param         $entity
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\NodeType",
     *     output="Wescape\CoreBundle\Entity\Node",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     200="Returned if the node is updated",
     *     401="Returned if the client is not authorized",
     *     404="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function patchAction(Request $request, Node $entity) {
        return $this->putAction($request, $entity);
    }

    /**
     * Delete a Node entity.
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param         $entity
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     input="Wescape\CoreBundle\Form\NodeType",
     *     output="Wescape\CoreBundle\Entity\Node",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     204="Returned if the node is deleted",
     *     401="Returned if the client is not authorized",
     *     404="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Node $entity) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
