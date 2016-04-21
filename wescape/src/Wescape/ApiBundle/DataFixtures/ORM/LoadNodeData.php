<?php

namespace Wescape\ApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wescape\CoreBundle\Entity\Node;

class LoadNodeData extends AbstractFixture implements OrderedFixtureInterface
{
    static public $nodes = [];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $node1 = new Node();
        $node1->setName("NODO1")
            ->setFloor("150")
            ->setMeterX(20)
            ->setMeterY(30)
            ->setX(200)
            ->setY(300)
            ->setWidth(2.4);

        $manager->persist($node1);

        $manager->flush();

        $this->addReference('node-1', $node1);

        self::$nodes = [
            $node1
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder() {
        return 1;
    }

}