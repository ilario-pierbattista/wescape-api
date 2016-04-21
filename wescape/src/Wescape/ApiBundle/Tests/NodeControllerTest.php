<?php

namespace Wescape\ApiBundle;

use Wescape\ApiBundle\DataFixtures\ORM\LoadNodeData;
use Wescape\CoreBundle\Test\WebTestCase;

class NodeControllerTest extends WebTestCase
{
    static public $expected = [
        '{"id": 1, "name":"NODO1", "floor":150, "meter_x":20, "meter_y": 30, "x":200, 
        "y": 300, "width": 2.4}'
    ];

    public function testGetAction() {
        $this->loadFixtures([LoadNodeData::class]);
        $client = self::createClient();

        $crawler = $client->request("GET", "api/v1/nodes/1.json");
        $this->assertStatusCode(200, $client);
    }
}