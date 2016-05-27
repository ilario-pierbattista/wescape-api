<?php

namespace Wescape\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Position
 *
 * @package Wescape\CoreBundle\Entity
 * @ORM\Entity()
 * @ORM\Table(name="position")
 */
class Position
{
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="Wescape\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @var Node
     * @ORM\ManyToOne(targetEntity="Wescape\CoreBundle\Entity\Node")
     */
    private $node;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param \Wescape\CoreBundle\Entity\User $user
     *
     * @return Position
     */
    public function setUser(\Wescape\CoreBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Wescape\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set node
     *
     * @param \Wescape\CoreBundle\Entity\Node $node
     *
     * @return Position
     */
    public function setNode(\Wescape\CoreBundle\Entity\Node $node = null)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return \Wescape\CoreBundle\Entity\Node
     */
    public function getNode()
    {
        return $this->node;
    }
}
