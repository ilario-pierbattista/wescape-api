<?php

namespace Wescape\CoreBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Wescape\CoreBundle\Entity\Edge;

class PositionRepository extends EntityRepository
{
    /**
     * Count user which are currently in the edge
     *
     * @param Edge $edge
     *
     * @return int
     */
    public function countUserInEdge(Edge $edge) {
        $users = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("p")
            ->from("CoreBundle:Position", "p")
            ->where("p.edge = :edge_id")
            ->setParameter("edge_id", $edge->getId())
            ->getQuery()
            ->getResult();
        return count($users);
    }
}