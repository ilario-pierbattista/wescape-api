<?php

namespace Wescape\CoreBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wescape\CoreBundle\Entity\Edge;

class LoadEdgeData extends AbstractFixture implements OrderedFixtureInterface
{
    private $edges = [];

    public function load(ObjectManager $manager) {
        $this->edges[] = (new Edge())
            ->setBegin($this->getReference('node-1'))
            ->setEnd($this->getReference('node-2'))
            ->setWidth(3.)
            ->setC(0)
            ->setI(0)
            ->setLos(0)
            ->setLength(20)
            ->setV(0);

        foreach ($this->edges as $e) {
            $manager->persist($e);
        }
        $manager->flush();

        foreach ($this->edges as $key => $edge) {
            $this->addReference("edge-".($key + 1), $edge);
        }
    }

    public function getOrder() {
        return 2;
    }
}