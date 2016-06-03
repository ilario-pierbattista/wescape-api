<?php

namespace Wescape\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class User
 *
 * @package Wescape\CoreBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @TODO    Escludere al serializzatore alcune informazioni
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
    private $resetTokenExpiresAt = null;

    /**
     * Identificativo del dispositivo usato dell'utente
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $deviceKey = null;

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
    public function getResetTokenExpiresAt() {
        return $this->resetTokenExpiresAt;
    }

    /**
     * @param \DateTime $resetTokenExpiresAt
     *
     * @return $this
     */
    public function setResetTokenExpiresAt($resetTokenExpiresAt) {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
        return $this;
    }

    /**
     * Set deviceKey
     *
     * @param string $deviceKey
     *
     * @return User
     */
    public function setDeviceKey($deviceKey)
    {
        $this->deviceKey = $deviceKey;

        return $this;
    }

    /**
     * Get deviceKey
     *
     * @return string
     */
    public function getDeviceKey()
    {
        return $this->deviceKey;
    }
}
