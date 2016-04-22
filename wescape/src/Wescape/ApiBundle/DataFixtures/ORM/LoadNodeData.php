<?php

namespace Wescape\ApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wescape\CoreBundle\Entity\Node;

class LoadNodeData extends AbstractFixture implements OrderedFixtureInterface
{
    private $nodes = [];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $this->nodes[] = (new Node())
            ->setName("NODO1")
            ->setFloor("150")
            ->setMeterX(20)
            ->setMeterY(30)
            ->setX(200)
            ->setY(300)
            ->setWidth(2.4);

        $this->nodes[] = (new Node())
            ->setName("NODO2")
            ->setFloor("150")
            ->setMeterX(50)
            ->setMeterY(30)
            ->setX(500)
            ->setY(300)
            ->setWidth(3.6);

        $this->nodes[] = (new Node())
            ->setName("NODO3")
            ->setFloor("150")
            ->setMeterX(20)
            ->setMeterY(60)
            ->setX(200)
            ->setY(600)
            ->setWidth(2.);
        
        $this->nodes[] = (new Node())
            ->setName("NODO4")
            ->setFloor("150")
            ->setMeterX(60)
            ->setMeterY(60)
            ->setX(600)
            ->setY(600)
            ->setWidth(3.);

        foreach ($this->nodes as $node) {
            $manager->persist($node);
        }
        $manager->flush();

        foreach ($this->nodes as $key => $node) {
            $this->addReference("node-".($key + 1), $node);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 1;
    }

}