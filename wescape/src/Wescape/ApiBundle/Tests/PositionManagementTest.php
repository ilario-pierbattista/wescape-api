<?php

namespace Wescape\ApiBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Wescape\CoreBundle\DataFixtures\ORM\LoadNodeData;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthClientTests;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthUsersTests;
use Wescape\CoreBundle\DataFixtures\ORM\LoadUsersPositons;
use Wescape\CoreBundle\Test\WebTestCase;

class PositionManagementTest extends WebTestCase
{
    private $client_data = [
        "id" => "1_" . LoadOAuthClientTests::RANDOM_ID,
        "secret" => LoadOAuthClientTests::SECRET
    ];

    /** @var Client */
    private $client;

    protected function setUp() {
        parent::setUp();
        $this->recreateDatabase();
        $this->loadFixtures([
            LoadOAuthUsersTests::class,
            LoadOAuthClientTests::class,
            LoadNodeData::class,
            LoadUsersPositons::class
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
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
        $this->client->request("GET", "/api/v1/users/2/position");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->printJsonContent($this->client);

        // Richiesta di un amministratore
        $this->client = $this->getAuthenticatedAdmin();
        $this->client->request("GET", "/api/v1/user/positions");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }
}