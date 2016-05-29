<?php

namespace Wescape\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Position
 *
 * @package Wescape\CoreBundle\Entity
 * @ORM\Entity(repositoryClass="Wescape\CoreBundle\Entity\Repository\PositionRepository")
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
     * @ORM\ManyToOne(targetEntity="Wescape\CoreBundle\Entity\Edge")
     */
    private $edge;

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
     * Set edge
     *
     * @param \Wescape\CoreBundle\Entity\Edge $edge
     *
     * @return Position
     */
    public function setEdge(\Wescape\CoreBundle\Entity\Edge $edge = null)
    {
        $this->edge = $edge;

        return $this;
    }

    /**
     * Get edge
     *
     * @return \Wescape\CoreBundle\Entity\Edge
     */
    public function getEdge()
    {
        return $this->edge;
    }
}
