<?php

namespace Wescape\ApiBundle\Tests;


use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Wescape\ApiBundle\DataFixtures\ORM\LoadEdgeData;
use Wescape\ApiBundle\DataFixtures\ORM\LoadNodeData;
use Wescape\CoreBundle\Test\WebTestCase;

class EdgeControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;
    /** @var EntityManager */
    private $em;
    /** @var Serializer */
    private $serializer;

    protected function setUp() {
        parent::setUp();
        $this->wipeDatabase();
        $this->loadFixtures([LoadEdgeData::class, LoadNodeData::class]);
        $this->client = self::createClient();
        $this->em = static::$kernel->getContainer()->get("doctrine")
            ->getManager();
        $this->serializer = SerializerBuilder::create()->build();
    }

    public function testGetAction() {
        $this->client->request("GET", "api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->request("GET", "api/v1/edges/2.json");
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testPostAction() {
        $edge = [
            "begin" => 1,
            "end" => 3,
            "width" => 2.2,
            "c" => 0,
            "los" => 0,
            "v" => 0,
            "i" => 0,
            "length" => 10
        ];

        $this->client->request("POST", "api/v1/edges.json", $edge);
        $responseData = json_decode($this->client->getResponse()->getContent(), TRUE);
        $this->assertStatusCode(Response::HTTP_CREATED, $this->client);

        $edge["begin"] = $this->getNodeInJson($edge["begin"]);
        $edge["end"] = $this->getNodeInJson($edge["end"]);

        $this->assertArraySubset($edge, $responseData);
    }

    /**
     * @depends testGetAction
     */
    public function testPutAction() {
        $edge = [
            "id" => 1,
            "begin" => 1,
            "end" => 4,
            "width" => 2.7,
            "c" => 0,
            "los" => 0,
            "v" => 0,
            "i" => 0,
            "length" => 15
        ];

        $this->client->request("PUT",  "api/v1/edges/1.json", $edge);
        $responseData = json_decode($this->client->getResponse()->getContent(), TRUE);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $edge["begin"] = $this->getNodeInJson($edge["begin"]);
        $edge["end"] = $this->getNodeInJson($edge["end"]);

        $this->assertArraySubset($edge, $responseData);
    }

    public function testDeleteAction() {
        $this->client->request("DELETE",  "api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, $this->client);
        $this->client->request("DELETE",  "api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    private function getNodeInJson($id) {
        $node = $this->em->getRepository("CoreBundle:Node")
            ->find($id);
        return json_decode($this->serializer->serialize($node, 'json'), TRUE);
    }
}