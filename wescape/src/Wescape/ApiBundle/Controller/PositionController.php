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
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Voryx\RESTGeneratorBundle\Controller\VoryxController;
use Wescape\CoreBundle\Entity\Position;
use Wescape\CoreBundle\Entity\User;
use Wescape\CoreBundle\Form\PositionType;
use Wescape\CoreBundle\Service\ErrorCodes;

/**
 * Position controller.
 * @RouteResource("Position")
 */
class PositionController extends VoryxController
{
    /**
     * Get a Position entity
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     * @ApiDoc(
     *     resource=true,
     *     output="Wescape\CoreBundle\Entity\Position",
     *     authenticationRoles={"ROLE_USER"},
     *     statusCodes={
     *     200="Returned if the position for the user is found",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     404="Returned if the user has no position",
     *     500="Returned if some general error occurs"}
     * )
     * @Security(
     * "has_role('ROLE_USER')"
     * )
     */
    public function getAction(User $user) {
        try {
            $em = $this->getDoctrine()->getManager();
            $position = $em->getRepository("CoreBundle:Position")
                ->findOneBy(['user' => $user->getId()]);
            $this->denyAccessUnlessGranted('view', $position);

            return $position;
        } catch (\Exception $e) {
            $code = Codes::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof AccessDeniedException) {
                $code = Codes::HTTP_FORBIDDEN;
            }
            return FOSView::create($e->getMessage(), $code);
        }
    }

    /**
     * Get all Position entities.
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
     *     resource=false,
     *     output="Wescape\CoreBundle\Entity\Position",
     *     authenticationRoles={"ROLE_ADMIN"},
     *     statusCodes={
     *     200="Returned in case of success",
     *     204="Returned if no one user has a position",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher) {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $limit = $limit == 0 ? null : $limit;
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('CoreBundle:Position')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                return $entities;
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a Position entity.
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     * @ApiDoc(
     *     resource=true,
     *     input="Wescape\CoreBundle\Form\PositionType",
     *     output="Wescape\CoreBundle\Entity\Position",
     *     authenticationRoles={"ROLE_USER"},
     *     statusCodes={
     *     201="Returned if the new position is setted",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     430="Returned if the user has already a position defined",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request) {
        try {
            $entity = new Position();
            $form = $this->createForm(get_class(new PositionType()), $entity, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted('create', $entity);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $existingPosition = $em->getRepository("CoreBundle:Position")
                    ->findOneBy(["user" => $entity->getUser()]);

                if (!empty($existingPosition)) {
                    return FOSView::create(["success" => false], ErrorCodes::POSITION_ALREADY_CREATED);
                }

                $em->persist($entity);
                $em->flush();

                return $entity;
            }
            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            $code = Codes::HTTP_INTERNAL_SERVER_ERROR;
            if ($code instanceof AccessDeniedException) {
                $code = Codes::HTTP_FORBIDDEN;
            }
            return FOSView::create($e->getMessage(), $code);
        }
    }

    /**
     * Update a Position entity.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     * @ApiDoc(
     *     resource=true,
     *     input="Wescape\CoreBundle\Form\PositionType",
     *     output="Wescape\CoreBundle\Entity\Position",
     *     authenticationRoles={"ROLE_USER"},
     *     statusCodes={
     *     200="Returned if the new position is updated",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     431="Returned if the user hasn't set the position yet",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_USER')")
     */
    public function putAction(Request $request, User $user) {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $position = $em->getRepository("CoreBundle:Position")
                ->findOneBy(['user' => $user->getId()]);
            if ($position === null) {
                return FOSView::create(["success" => false], ErrorCodes::POSITION_NOT_FOUND);
            }

            $form = $this->createForm(get_class(new PositionType()), $position, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);

            $this->denyAccessUnlessGranted('edit', $user);
            $this->denyAccessUnlessGranted('edit', $position);

            if ($form->isValid()) {
                $em->flush();

                return $position;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            $code = Codes::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof AccessDeniedException) {
                $code = Codes::HTTP_FORBIDDEN;
            }
            return FOSView::create($e->getMessage(), $code);
        }
    }

    /**
     * Partial Update to a Position entity.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     */
    public function patchAction(Request $request, User $user) {
        return $this->putAction($request, $user);
    }

    /**
     * Delete a Position entity.
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     * @ApiDoc(
     *     resource=false,
     *     authenticationRoles={"ROLE_USER"},
     *     statusCodes={
     *     204="Returned if the position is deleted",
     *     401="Returned if the client is not authorized",
     *     403="Returned if the user doesn't have the correct privileges",
     *     431="Returned if the user has not a position to delete",
     *     500="Returned if some general error occurs"}
     * )
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteAction(Request $request, User $user) {
        try {
            $em = $this->getDoctrine()->getManager();
            $position = $em->getRepository("CoreBundle:Position")->findOneBy(['user' => $user->getId()]);

            $this->denyAccessUnlessGranted('edit', $user);
            if ($position === null) {
                return FOSView::create(['success' => false], ErrorCodes::POSITION_NOT_FOUND);
            }
            $this->denyAccessUnlessGranted('delete', $position);

            $em->remove($position);
            $em->flush();

            return null;
        } catch (\Exception $e) {
            $code = Codes::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof AccessDeniedException) {
                $code = Codes::HTTP_FORBIDDEN;
            }
            return FOSView::create($e->getMessage(), $code);
        }
    }
}
