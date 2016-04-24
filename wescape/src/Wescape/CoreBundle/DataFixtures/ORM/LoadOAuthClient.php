<?php

namespace Wescape\CoreBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wescape\CoreBundle\Entity\Client;

class LoadOAuthClient extends AbstractFixture implements OrderedFixtureInterface
{
    const RANDOM_ID = "3bcbxd9e24g0gk4swg0kwgcwg4o8k8g4g888kwc44gcc0gwwk4";
    const SECRET = "4ok2x70rlfokc8g0wws8c8kwcokw80k44sg48goc0ok4w0so0k";

    public function load(ObjectManager $manager) {
        $client = new Client();
        $client->setRandomId(self::RANDOM_ID);
        $client->setSecret(self::SECRET);
        $client->setAllowedGrantTypes(["password"]);

        $manager->persist($client);
        $manager->flush();
        $this->addReference("client", $client);
    }

    public function getOrder() {
        return 0;
    }
}