<?php

namespace Wescape\CoreBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wescape\CoreBundle\Entity\Edge;

class LoadAdditionalEdges extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager) {
        $edge2 = (new Edge())
            ->setBegin($this->getReference('node-2'))
            ->setEnd($this->getReference('node-3'))
            ->setWidth(3.)
            ->setC(0)
            ->setI(0)
            ->setLos(0)
            ->setLength(20)
            ->setV(0);
        $manager->persist($edge2);

        $edge3 = (new Edge())
            ->setBegin($this->getReference('node-1'))
            ->setEnd($this->getReference('node-3'))
            ->setWidth(3.)
            ->setC(0)
            ->setI(0)
            ->setLos(0)
            ->setLength(20)
            ->setV(0);

        $manager->persist($edge3);
        $manager->flush();

        $this->addReference('edge-2', $edge2);
        $this->addReference('edge-3', $edge3);
    }

    public function getOrder() {
        return 2;
    }
}