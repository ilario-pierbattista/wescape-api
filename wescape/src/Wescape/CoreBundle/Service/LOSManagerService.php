<?php

namespace Wescape\CoreBundle\Service;


use Doctrine\ORM\EntityManager;
use Wescape\CoreBundle\Entity\Edge;

class LOSManagerService
{
    private static $VERY_LOW_AREA = 0.75;
    private static $LOW_AREA = 1.4;
    private static $MEDIUM_AREA = 2.2;
    private static $LARGE_AREA = 3.7;

    private static $VERY_HIGH_LOS = 3;
    private static $HIGH_LOS = 1;
    private static $MEDIUM_LOS = 0.67;
    private static $LOW_LOS = 0.33;
    private static $VERY_LOW_LOS = 0;

    /** @var  EntityManager */
    private $em;

    /**
     * LOSManagerService constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }

    /**
     * Update the LOS parameter of an edge
     *
     * @param Edge $edge
     *
     * @return $this
     */
    public function updateEdge(Edge $edge) {
        $los = $this->calculateLOS($edge);
        $edge->setLos($los);

        $this->em->persist($edge);
        $this->em->flush();

        return $this;
    }

    /**
     * It calculates the LOS parameter of an edge
     *
     * @param Edge $edge
     *
     * @return float|int
     */
    private function calculateLOS(Edge $edge) {
        $peoplePresent = $this->em->getRepository("CoreBundle:Position")
            ->countUserInEdge($edge);
        // Il LOS andrebbe calcolato per le persone che si apprestano ad entrare in un arco
        // quindi bisogna aggiungere un'unità al conteggio delle persone nell'arco.
        $peoplePresent += 1;

        $areaPerPerson = $edge->getArea() / $peoplePresent;

        if ($areaPerPerson < self::$VERY_LOW_AREA) {
            return self::$VERY_HIGH_LOS;
        } else if (self::$VERY_LOW_AREA <= $areaPerPerson && $areaPerPerson < self::$LOW_AREA) {
            return self::$HIGH_LOS;
        } else if (self::$LOW_AREA <= $areaPerPerson && $areaPerPerson < self::$MEDIUM_AREA) {
            return self::$MEDIUM_LOS;
        } else if (self::$MEDIUM_AREA <= $areaPerPerson && $areaPerPerson < self::$LARGE_AREA) {
            return self::$LOW_LOS;
        } else {
            return self::$VERY_LOW_LOS;
        }
    }
}