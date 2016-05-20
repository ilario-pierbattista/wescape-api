<?php

namespace Wescape\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View as FOSView;
use FOS\UserBundle\Model\UserManager;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Voryx\RESTGeneratorBundle\Controller\VoryxController;
use Wescape\CoreBundle\Entity\User;
use Wescape\CoreBundle\Form\CreateUserType;
use Wescape\CoreBundle\Form\RequestResetPasswordType;
use Wescape\CoreBundle\Form\ResetPasswordType;
use Wescape\CoreBundle\Form\UserType;
use Wescape\CoreBundle\Service\ErrorCodes;
use Wescape\CoreBundle\Service\PasswordResetService;

/**
 * User controller.
 * @RouteResource("User")
 */
class UserController extends VoryxController
{
    /**
     * Get a User entity
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     * @ApiDoc(resource=true)
     * @Security(
     * "has_role('ROLE_ADMIN') || (has_role('ROLE_USER') && user.getId()==entity.getId())"
     * )
     */
    public function getAction(User $entity) {
        return $entity;
    }

    /**
     * Get all User entities.
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
     * @ApiDoc(resource=true)
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher) {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit') != 0 ? $paramFetcher->get('limit') : null;
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('CoreBundle:User')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                return $entities;
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a User entity.
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     * @ApiDoc(resource=true)
     */
    public function postAction(Request $request) {
        /** @var UserManager $userManager */
        $userManager = $this->get("fos_user.user_manager");
        $user = $userManager->createUser();
        $form = $this->createForm(get_class(new CreateUserType()), $user, array("method" => $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->setUsername($user->getEmail())
                ->setRoles(['ROLE_USER'])
                ->setEnabled(true);

            // Gestione degli errori prevedibili
            if ($userManager->findUserByEmail($user->getEmail()) != null) {
                return FOSView::create(["success" => false], ErrorCodes::SIGNUP_DUPLICATED_EMAIL);
            }

            $userManager->updateUser($user);

            return $user;
        }

        return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Update a User entity.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param         $entity
     *
     * @return Response
     * @ApiDoc(resource=true)
     * @Security(
     * "has_role('ROLE_ADMIN') || (has_role('ROLE_USER') && user.getId()==entity.getId())"
     * )
     */
    public function putAction(Request $request, User $entity) {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(get_class(new UserType()), $entity, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);

            if ($form->isValid()) {
                $entity->setUsername($entity->getEmail());
                $em->flush();

                return $entity;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partial Update to a User entity.
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param         $entity
     *
     * @return Response
     * @ApiDoc(resource=true)
     * @Security(
     * "has_role('ROLE_ADMIN') || (has_role('ROLE_USER') && user.getId()==entity.getId())"
     * )
     */
    public function patchAction(Request $request, User $entity) {
        return $this->putAction($request, $entity);
    }

    /**
     * Delete a User entity.
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param         $entity
     *
     * @return Response
     * @ApiDoc(resource=true)
     * @Security("has_role('ROLE_ADMIN') && user.getId() != entity.getId()")
     */
    public function deleteAction(Request $request, User $entity) {
        try {
            /** @var UserManager $userManager */
            $userManager = $this->get("fos_user.user_manager");
            $userManager->deleteUser($entity);

            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Richiede il codice per reimpostare la password
     * @View(statusCode=202)
     *
     * @param Request $request
     *
     * @return Response
     * @Post("/users/password/request")
     */
    public function requestPasswordResetAction(Request $request) {
        try {
            $form = $this->createForm(
                get_class(new RequestResetPasswordType()),
                null,
                array("method" => $request->getMethod()));

            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var PasswordResetService $passwordResetService */
                $passwordResetService = $this->container->get("core.password_reset");
                $passwordResetService->request($form->get('email')->getData());

                return FOSView::create(["success" => true], Codes::HTTP_ACCEPTED);
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            switch ($e->getCode()) {
                case PasswordResetService::USER_NOT_FOUND_MSG: {
                    $errorCode = ErrorCodes::PASSWORD_RESET_WRONG_EMAIL;
                    break;
                }
                default:
                    $errorCode = Codes::HTTP_INTERNAL_SERVER_ERROR;
            }
            return FOSView::create($e->getMessage(), $errorCode);
        }
    }

    /**
     * Reimposta la password dell'utente
     *
     * @param Request $request
     *
     * @return Response
     * @Post("/users/password/reset")
     */
    public function resetPasswordAction(Request $request) {
        try {
            $form = $this->createForm(
                get_class(new ResetPasswordType()),
                null,
                array("method" => $request->getMethod()));

            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var PasswordResetService $passwordResetService */
                $passwordResetService = $this->container->get("core.password_reset");

                $resetToken = $form->get("reset_password_token")->getData();
                $newPassword = $form->get("new_password")->getData();

                $user = $passwordResetService->reset($form->get('email')->getData(),
                    $resetToken, $newPassword);
                return $user;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            switch ($e->getMessage()) {
                case PasswordResetService::USER_NOT_FOUND_MSG: {
                    $errorCode = ErrorCodes::PASSWORD_RESET_WRONG_EMAIL;
                    break;
                }
                case PasswordResetService::INVALID_SECRET_TOKEN_MSG: {
                    $errorCode = ErrorCodes::PASSWORD_RESET_WRONG_SECRET_CODE;
                    break;
                }
                case PasswordResetService::EXPIRED_SECRET_TOKEN_MSG: {
                    $errorCode = ErrorCodes::PASSWORD_RESET_EXPIRED_SECRET;
                    break;
                }
                default:
                    $errorCode = Codes::HTTP_INTERNAL_SERVER_ERROR;
            }
            return FOSView::create($e->getMessage(), $errorCode);
        }
    }
}
