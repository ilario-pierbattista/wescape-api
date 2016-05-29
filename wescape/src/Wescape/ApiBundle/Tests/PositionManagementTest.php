<?php

namespace Wescape\ApiBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Wescape\CoreBundle\DataFixtures\ORM\LoadAdditionalEdges;
use Wescape\CoreBundle\DataFixtures\ORM\LoadEdgeData;
use Wescape\CoreBundle\DataFixtures\ORM\LoadNodeData;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthClientTests;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthUsersTests;
use Wescape\CoreBundle\DataFixtures\ORM\LoadUsersPositons;
use Wescape\CoreBundle\Service\ErrorCodes;
use Wescape\CoreBundle\Test\WebTestCase;

class PositionManagementTest extends WebTestCase
{
    /** @var Client */
    private $client;

    protected function setUp() {
        parent::setUp();
        $this->recreateDatabase();
        $this->loadFixtures([
            LoadOAuthUsersTests::class,
            LoadOAuthClientTests::class,
            LoadNodeData::class,
            LoadEdgeData::class,
            LoadUsersPositons::class,
            LoadAdditionalEdges::class
        ]);
        $this->client = self::createClient();
    }

    public function testGetAction() {
        // Richiesta anonima
        $this->client->request("GET", "/api/v1/user/positions");
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);
        $this->client->request("GET", "/api/v1/users/1/position");
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);
        $this->client->request("GET", "/api/v1/users/2/position");
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Richiesta da utente autenticato
        $this->client = $this->getAuthenticatedUser();
        $this->client->request("GET", "/api/v1/user/positions");
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
        $this->client->request("GET", "/api/v1/users/1/position");
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
        $this->client->request("GET", "/api/v1/users/2/position");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);


        // Richiesta di un amministratore
        $this->client = $this->getAuthenticatedAdmin();
        $this->client->request("GET", "/api/v1/user/positions");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->client->request("GET", "/api/v1/users/1/position");
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, $this->client);
        $this->client->request("GET", "/api/v1/users/2/position");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->client->request("GET", "/api/v1/users/5/position");
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }
    
    public function testPostAction() {
        $positionUser = [
            "user" => 2,
            "edge" => 1
        ];

        $positionOtherUser = [
            "user" => 3,
            "edge" => 2
        ];

        // Richiesta anonima
        $this->client->request("POST", "/api/v1/users/positions", $positionUser);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Utente 2
        $this->client = $this->getAuthenticatedUser();
        $this->client->request("POST", "/api/v1/users/positions", $positionUser);
        $this->assertStatusCode(ErrorCodes::POSITION_ALREADY_CREATED, $this->client);
        $this->client->request("POST", "/api/v1/users/positions", $positionOtherUser);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        //Utente 3
        $this->client = $this->getAuthenticatedUser("test2@wescape.it", "test2");
        $this->client->request("POST", "/api/v1/users/positions", $positionOtherUser);
        $this->assertStatusCode(Response::HTTP_CREATED, $this->client);

        // Admin
        $this->client = $this->getAuthenticatedAdmin();
        $this->client->request("POST", "/api/v1/users/positions", $positionUser);
        $this->assertStatusCode(ErrorCodes::POSITION_ALREADY_CREATED, $this->client);
        $positionOtherUser["user"] = 4;
        $this->client->request("POST", "/api/v1/users/positions", $positionOtherUser);
        $this->assertStatusCode(Response::HTTP_CREATED, $this->client);
    }

    public function testPutAction() {
        $positionOne = ["user" => 2, "edge" => 2];
        $positionTwo = ["user" => 2, "edge" => 3];
        $positionFour = ["user" => 4, "edge" => 2];

        // Richiesta anonima
        $this->client->request("PUT", "/api/v1/users/2/position", $positionOne);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Utente 2
        $this->client = $this->getAuthenticatedUser();
        $this->client->request("PUT", "/api/v1/users/2/position", $positionOne);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        // Utente 3
        $this->client = $this->getAuthenticatedUser("test2@wescape.it", "test2");
        $this->client->request("PUT", "/api/v1/users/2/position", $positionTwo);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
        $this->client->request("PUT", "/api/v1/users/3/position", $positionTwo);
        $this->assertStatusCode(ErrorCodes::POSITION_NOT_FOUND, $this->client);

        // Utente 4
        $this->client = $this->getAuthenticatedUser("user3", "user3");
        $this->client->request("PUT", "/api/v1/users/4/position", $positionFour);
        $this->assertStatusCode(ErrorCodes::POSITION_NOT_FOUND, $this->client);

        // Admin
        $this->client = $this->getAuthenticatedAdmin();
        $this->client->request("PUT", "/api/v1/users/2/position", $positionTwo);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }
    
    public function testDeleteAction() {
        // Non autenticato
        $this->client->request("DELETE", "/api/v1/users/2/position");
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Utente 3
        $this->client = $this->getAuthenticatedUser("test2@wescape.it", "test2");
        $this->client->request("DELETE", "/api/v1/users/2/position");
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        // Utente 2
        $this->client = $this->getAuthenticatedUser();
        $this->client->request("DELETE", "/api/v1/users/2/position");
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, $this->client);

        // Admin
        $this->client = $this->getAuthenticatedAdmin();
        $this->client->request("DELETE", "/api/v1/users/2/position");
        $this->assertStatusCode(ErrorCodes::POSITION_NOT_FOUND, $this->client);
    }
}