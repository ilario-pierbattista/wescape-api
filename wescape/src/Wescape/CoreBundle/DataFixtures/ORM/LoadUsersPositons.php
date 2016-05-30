<?php

namespace Wescape\CoreBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wescape\CoreBundle\Entity\Position;

class LoadUsersPositons extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager) {
        $position1 = new Position();
        $position1->setEdge($this->getReference('edge-1'))
            ->setUser($this->getReference('user1'));

        $manager->persist($position1);
        $manager->flush();
    }

    public function getOrder() {
        return 4;
    }
}