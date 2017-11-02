<?php

namespace Wescape\CoreBundle\Security;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\TraceableAccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wescape\CoreBundle\Entity\User;

class UserVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';

    private $supportedAttributes = [
        self::EDIT,
        self::DELETE
    ];
    private $decisionManager;

    public function __construct(TraceableAccessDecisionManager $decisionManager) {
        $this->decisionManager = $decisionManager;
    }


    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject) {
        if (!in_array($attribute, $this->supportedAttributes)) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        /** @var User $agent */
        $agent = $token->getUser();

        if (!$agent instanceof User) {
            return false;
        }

        /** @var User $userSubject */
        $userSubject = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->isAdmin($token) || $this->canEdit($userSubject, $agent);
            case self::DELETE:
                return $this->isAdmin($token) && $this->canDelete($userSubject, $agent);
        }

        throw new \LogicException("This code should not be reached");
    }

    /**
     * @param User $subjectUser
     * @param User $agent
     *
     * @return bool
     */
    private function canEdit(User $subjectUser, User $agent) {
        return $agent->getId() == $subjectUser->getId();
    }

    /**
     * @param User $subjectUSer
     * @param User $agent
     *
     * @return bool
     */
    private function canDelete(User $subjectUSer, User $agent) {
        return $agent->getId() != $subjectUSer->getId();
    }

    private function isAdmin($token) {
        return $this->decisionManager->decide($token, array('ROLE_ADMIN'));
    }
}