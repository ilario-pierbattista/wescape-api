<?php

namespace Wescape\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class represent a vertex of the graph, modelling the map
 *
 * @package Wescape\CoreBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="node")
 */
class Node
{
    const TYPE_GENERAL = "G";
    const TYPE_ROOM = "R";
    const TYPE_EMERGENCY = "E";
    const TYPE_EXIT = "U";

    /**
     * Node identifier
     * @ORM\Column(type="integer")
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * Node name
     * @ORM\Column(type="string", length=30)
     *
     * @var string
     */
    private $name;

    /**
     * X-axis pixel coordinate of the point in the map image
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $x;

    /**
     * Y-axis pixel coordinate of the point in the map image
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $y;

    /**
     * Floor of the node
     * @ORM\Column(type="string", columnDefinition="enum('145', '150', '155')")
     *
     * @var integer
     */
    private $floor;

    /**
     * Node width
     * @ORM\Column(type="decimal", scale=2)
     *
     * @var integer
     */
    private $width;

    /**
     * X-axis coordinate in meters
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $meter_x;

    /**
     * Y-axis coordinate in meters
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $meter_y;

    /**
     * Node type (one of 'R', 'U', 'E', 'G')
     * @ORM\Column(type="string", columnDefinition="enum('R', 'U', 'E', 'G')")
     *
     * @var string
     */
    private $type;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Node
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set x
     *
     * @param integer $x
     *
     * @return Node
     */
    public function setX($x) {
        $this->x = $x;

        return $this;
    }

    /**
     * Get x
     *
     * @return integer
     */
    public function getX() {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param integer $y
     *
     * @return Node
     */
    public function setY($y) {
        $this->y = $y;

        return $this;
    }

    /**
     * Get y
     *
     * @return integer
     */
    public function getY() {
        return $this->y;
    }

    /**
     * Set floor
     *
     * @param string $floor
     *
     * @return Node
     */
    public function setFloor($floor) {
        $this->floor = $floor;

        return $this;
    }

    /**
     * Get floor
     *
     * @return string
     */
    public function getFloor() {
        return $this->floor;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return Node
     */
    public function setWidth($width) {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * Set meterX
     *
     * @param integer $meterX
     *
     * @return Node
     */
    public function setMeterX($meterX) {
        $this->meter_x = $meterX;

        return $this;
    }

    /**
     * Get meterX
     *
     * @return integer
     */
    public function getMeterX() {
        return $this->meter_x;
    }

    /**
     * Set meterY
     *
     * @param integer $meterY
     *
     * @return Node
     */
    public function setMeterY($meterY) {
        $this->meter_y = $meterY;

        return $this;
    }

    /**
     * Get meterY
     *
     * @return integer
     */
    public function getMeterY() {
        return $this->meter_y;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Node
     */
    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }
}
