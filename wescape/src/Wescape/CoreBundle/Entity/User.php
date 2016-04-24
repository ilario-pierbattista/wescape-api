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
     * @return int
     */
    public function getId() {
        return $this->id;
    }
}