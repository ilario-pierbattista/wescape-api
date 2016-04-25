<?php

namespace Wescape\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class User
 *
 * @package Wescape\CoreBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends \FOS\UserBundle\Model\User
{
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $resetPasswordToken;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resetRequestedAt = null;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getResetPasswordToken() {
        return $this->resetPasswordToken;
    }

    /**
     * @param string $resetPasswordToken
     *
     * @return $this
     */
    public function setResetPasswordToken($resetPasswordToken) {
        $this->resetPasswordToken = $resetPasswordToken;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getResetRequestedAt() {
        return $this->resetRequestedAt;
    }

    /**
     * @param \DateTime $resetRequestedAt
     *
     * @return $this
     */
    public function setResetRequestedAt($resetRequestedAt) {
        $this->resetRequestedAt = $resetRequestedAt;
        return $this;
    }
}