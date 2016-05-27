<?php

namespace Wescape\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Edge
 * Rappresenta gli archi del grafo.
 * Sebbene il grafo non sia orientato, gli estremi di ogni arco sono comunque
 * soprannominati <pre>begin</pre> ed <pre>end</pre>.
 *
 * @package Wescape\CoreBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="edge")
 */
class Edge
{
    /**
     * Edge identifier
     * @ORM\Column(type="integer")
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * The beginning vertex of the edge
     * @ORM\ManyToOne(targetEntity="Node")
     * @ORM\JoinColumn(name="begin", referencedColumnName="id")
     *
     * @var Node
     */
    private $begin;

    /**
     * The ending vertex of the edge
     * @ORM\ManyToOne(targetEntity="Node")
     * @ORM\JoinColumn(name="end", referencedColumnName="id")
     *
     * @var Node
     */
    private $end;

    /**
     * Length of the edge
     * @ORM\Column(type="decimal", scale=2)
     *
     * @var double
     */
    private $length;

    /**
     * Average width of the edge
     * @ORM\Column(type="decimal", scale=2)
     *
     * @var double
     */
    private $width;

    /**
     * Flag for knowing if the edge represents a stairs or not
     * @ORM\Column(type="boolean")
     *
     * @var boolean
     */
    private $stairs = false;

    /**
     * Risk of development of fires
     * @ORM\Column(type="decimal", scale=4)
     *
     * @var double
     */
    private $v;

    /**
     * Risk of development of toxicological chain reactions
     * @ORM\Column(type="decimal", scale=4)
     *
     * @var double
     */
    private $i;

    /**
     * Average portions of area per person
     * @ORM\Column(type="decimal", scale=4)
     *
     * @var double
     */
    private $los;

    /**
     * Smoke presence parameter
     * @ORM\Column(type="decimal", scale=4)
     *
     * @var double
     */
    private $c;

    /**
     * @return float Area del tronco
     */
    public function getArea() {
        return $this->width * $this->length;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set v
     *
     * @param string $v
     *
     * @return Edge
     */
    public function setV($v) {
        $this->v = $v;

        return $this;
    }

    /**
     * Get v
     *
     * @return string
     */
    public function getV() {
        return $this->v;
    }

    /**
     * Set i
     *
     * @param string $i
     *
     * @return Edge
     */
    public function setI($i) {
        $this->i = $i;

        return $this;
    }

    /**
     * Get i
     *
     * @return string
     */
    public function getI() {
        return $this->i;
    }

    /**
     * Set los
     *
     * @param string $los
     *
     * @return Edge
     */
    public function setLos($los) {
        $this->los = $los;

        return $this;
    }

    /**
     * Get los
     *
     * @return string
     */
    public function getLos() {
        return $this->los;
    }

    /**
     * Set c
     *
     * @param string $c
     *
     * @return Edge
     */
    public function setC($c) {
        $this->c = $c;

        return $this;
    }

    /**
     * Get c
     *
     * @return string
     */
    public function getC() {
        return $this->c;
    }

    /**
     * Set begin
     *
     * @param \Wescape\CoreBundle\Entity\Node $begin
     *
     * @return Edge
     */
    public function setBegin(\Wescape\CoreBundle\Entity\Node $begin = null) {
        $this->begin = $begin;

        return $this;
    }

    /**
     * Get begin
     *
     * @return \Wescape\CoreBundle\Entity\Node
     */
    public function getBegin() {
        return $this->begin;
    }

    /**
     * Set end
     *
     * @param \Wescape\CoreBundle\Entity\Node $end
     *
     * @return Edge
     */
    public function setEnd(\Wescape\CoreBundle\Entity\Node $end = null) {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \Wescape\CoreBundle\Entity\Node
     */
    public function getEnd() {
        return $this->end;
    }

    /**
     * Set length
     *
     * @param string $length
     *
     * @return Edge
     */
    public function setLength($length) {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return string
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * Set width
     *
     * @param string $width
     *
     * @return Edge
     */
    public function setWidth($width) {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return string
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * Set stairs
     *
     * @param boolean $stairs
     *
     * @return Edge
     */
    public function setStairs($stairs) {
        $this->stairs = $stairs;

        return $this;
    }

    /**
     * Get stairs
     *
     * @return boolean
     */
    public function getStairs() {
        return $this->stairs;
    }
}
