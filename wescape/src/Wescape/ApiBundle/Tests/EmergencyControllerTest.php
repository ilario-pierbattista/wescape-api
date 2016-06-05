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

class EmergencyControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    protected function setUp() {
        parent::setUp();
        $this->recreateDatabase();
        $this->loadFixtures([
            LoadOAuthUsersTests::class,
            LoadOAuthClientTests::class,
        ]);
        $this->client = self::createClient();
    }

    public function testGetAction() {
        // Richiesta anonima
        $this->client->request("GET", "/api/v1/emergency");
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        // Richiesta da utente autenticato
        $this->client = $this->getAuthenticatedUser();
        $this->client->request("GET", "/api/v1/emergency");
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        // Richiesta di un amministratore
        $this->client = $this->getAuthenticatedAdmin();
        $this->client->request("GET", "/api/v1/emergency");
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }
}