<?php

namespace Wescape\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Node
 *
 * @package Wescape\CoreBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="node")
 */
class Node
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer Id numerico
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @var string Nome del nodo
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @var integer Coordinata x rispetto all'immagine della mappa
     */
    private $x;

    /**
     * @ORM\Column(type="integer")
     * @var integer Coordinata y rispetto all'immagine della mappa
     */
    private $y;

    /**
     * @ORM\Column(type="string", columnDefinition="enum('145', '150', '155')")
     * @var integer Piano
     */
    private $floor;

    /**
     * @ORM\Column(type="decimal", scale=2)
     * @var integer Larghezza del nodo in metri
     */
    private $width;

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
}
