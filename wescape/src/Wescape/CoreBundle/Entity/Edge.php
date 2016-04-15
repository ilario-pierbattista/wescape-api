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
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer Id
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Node")
     * @ORM\JoinColumn(name="begin", referencedColumnName="id")
     * @var Node Estremo iniziale dell'arco
     */
    private $begin;

    /**
     * @ORM\ManyToOne(targetEntity="Node")
     * @ORM\JoinColumn(name="end", referencedColumnName="id")
     * @var Node Estremo finale dell'arco
     */
    private $end;

    /**
     * @ORM\Column(type="decimal", precision=2)
     * @var double Lunghezza del tronco in metri
     */
    private $length;

    /**
     * @ORM\Column(type="decimal", precision=2)
     * @var double Larghezza media del tronco in metri
     */
    private $width;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean Se il tronco rappresenta una scala, questo flag è impostato a TRUE,
     * altrimenti a FALSE
     */
    private $stairs = false;

    /**
     * @ORM\Column(type="decimal", precision=4)
     * @var double Propensione allo sviluppo di incendi
     */
    private $v;

    /**
     * @ORM\Column(type="decimal", precision=4)
     * @var double Reazioni a catena tossicologiche
     */
    private $i;

    /**
     * @ORM\Column(type="decimal", precision=4)
     * @var double Superficie calpestabile disponibile ad una persona lungo il tratto
     * di evaquazione
     */
    private $los;

    /**
     * @ORM\Column(type="decimal", precision=4)
     * @var double Parametro di presenza di fumo
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
