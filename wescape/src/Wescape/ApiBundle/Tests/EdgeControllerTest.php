<?php

namespace Wescape\ApiBundle\Tests;


use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Wescape\ApiBundle\DataFixtures\ORM\LoadEdgeData;
use Wescape\ApiBundle\DataFixtures\ORM\LoadNodeData;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthClient;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthUsers;
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
        $this->recreateDatabase();
        $this->loadFixtures([LoadEdgeData::class, LoadNodeData::class, LoadOAuthUsers::class, LoadOAuthClient::class]);
        $this->client = self::createClient();
        $this->em = static::$kernel->getContainer()->get("doctrine")
            ->getManager();
        $this->serializer = SerializerBuilder::create()->build();
    }

    public function testGetAction() {
        // Anonimo
        $this->client->request("GET", "/api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Utente
        $this->client = $this->getAuthenticatedUser();

        $this->client->request("GET", "/api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->client->request("GET", "/api/v1/edges/2.json");
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

        // Anonimo
        $this->client->request("POST", "/api/v1/edges.json", $edge);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Utente
        $this->client = $this->getAuthenticatedUser();

        $this->client->request("POST", "/api/v1/edges.json", $edge);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        // Admin
        $this->client = $this->getAuthenticatedAdmin();

        $this->client->request("POST", "/api/v1/edges.json", $edge);
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

        // Anonimo
        $this->client->request("PUT", "/api/v1/edges/1.json", $edge);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Utente
        $this->client = $this->getAuthenticatedUser();

        $this->client->request("PUT", "/api/v1/edges/1.json", $edge);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        // Admin
        $this->client = $this->getAuthenticatedAdmin();

        $this->client->request("PUT", "/api/v1/edges/1.json", $edge);
        $responseData = json_decode($this->client->getResponse()->getContent(), TRUE);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $edge["begin"] = $this->getNodeInJson($edge["begin"]);
        $edge["end"] = $this->getNodeInJson($edge["end"]);
        $this->assertArraySubset($edge, $responseData);
    }

    public function testDeleteAction() {
        // Anonimo
        $this->client->request("DELETE", "/api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Utente
        $this->client = $this->getAuthenticatedUser();

        $this->client->request("DELETE", "/api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        // Admin
        $this->client = $this->getAuthenticatedAdmin();

        $this->client->request("DELETE", "/api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, $this->client);
        $this->client->request("DELETE", "/api/v1/edges/1.json");
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    private function getNodeInJson($id) {
        $node = $this->em->getRepository("CoreBundle:Node")
            ->find($id);
        return json_decode($this->serializer->serialize($node, 'json'), TRUE);
    }
}