<?php

namespace Wescape\ApiBundle;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Wescape\ApiBundle\DataFixtures\ORM\LoadNodeData;
use Wescape\CoreBundle\Test\WebTestCase;

class NodeControllerTest extends WebTestCase
{
    static public $expected = [
        1 => '{"id":1,"name":"NODO1","x":200,"y":300,"floor":"150","width":2.4,"meter_x":20,"meter_y":30}',
    ];

    /**
     * @var Client
     */
    private $client;

    protected function setUp() {
        parent::setUp();
        $this->loadFixtures([LoadNodeData::class]);
        $this->client = self::createClient();
    }


    public function testGetAction() {
        // Test di una richiesta con successo
        $this->client->request("GET", "api/v1/nodes/1.json");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertEquals(self::$expected[1], $this->client->getResponse()->getContent());

        // Test di una richiesta che sicuramente fallirà
        $this->client->request("GET", "api/v1/nodes/1000.json");
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testPostAction() {
        $node = [
            'name' => "POST_TEST",
            'x' => 100,
            'y' => 200,
            'floor' => "150",
            'width' => 1.5,
            'meter_x' => 10,
            'meter_y' => 20
        ];

        $this->client->request("POST", "api/v1/nodes.json", $node);
        $responseData = json_decode($this->client->getResponse()->getContent(), TRUE);
        $this->assertStatusCode(Response::HTTP_CREATED, $this->client);
        $this->assertArraySubset($node, $responseData);
    }

    public function testPutAction() {
        $node = [
            'id' => 1,
            'name' => "PUT_TEST",
            'x' => 100,
            'y' => 200,
            'floor' => "150",
            'width' => 1.5,
            'meter_x' => 10,
            'meter_y' => 20
        ];

        $this->client->request("PUT", "api/v1/nodes/1.json", $node);
        $responseData = json_decode($this->client->getResponse()->getContent(), TRUE);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertArraySubset($node, $responseData);
        $this->assertArraySubset($responseData, $node);
    }

    public function testDeleteAction() {
        $this->client->request("DELETE", "api/v1/nodes/1.json");
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, $this->client);
        $this->client->request("GET", "api/v1/nodes/1.json");
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }
}