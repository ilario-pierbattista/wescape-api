<?php

namespace Wescape\CoreBundle\Security;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wescape\CoreBundle\Entity\Position;
use Wescape\CoreBundle\Entity\User;

class PositionVoter extends Voter
{
    const EDIT = 'edit';
    const VIEW = 'view';
    const CREATE = 'create';

    private $supportedAttributes = [
        self::EDIT,
        self::VIEW,
        self::CREATE
    ];
    private $decisionManager;

    public function __construct(AccessDecisionManager $decisionManager) {
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

        if (!$subject instanceof Position && $subject != null) {
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

        /** @var Position $position */
        $position = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->isAdmin($token) || $this->canEdit($position, $agent);
            case self::VIEW:
                return $this->isAdmin($token) || $this->canView($position, $agent);
            case self::CREATE:
                return $this->isAdmin($token) || $this->canCreate($position, $agent);
        }

        throw new \LogicException("This code should not be reached");
    }

    /**
     * @param Position $position
     * @param User     $agent
     *
     * @return bool
     */
    private function canEdit($position, User $agent) {
        return $position !== null && $agent->getId() == $position->getUser()->getId();
    }

    /**
     * @param Position $position
     * @param User     $agent
     *
     * @return bool
     */
    private function canView($position, User $agent) {
        return $position !== null && $agent->getId() == $position->getUser()->getId();
    }

    /**
     * @param Position $position
     * @param User     $agent
     *
     * @return bool
     */
    private function canCreate(Position $position, User $agent) {
        return $agent->getId() == $position->getUser()->getId();
    }

    private function isAdmin($token) {
        return $this->decisionManager->decide($token, array('ROLE_ADMIN'));
    }
}