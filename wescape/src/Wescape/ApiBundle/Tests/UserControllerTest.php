<?php

namespace Wescape\ApiBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthClientTests;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthUsersTests;
use Wescape\CoreBundle\Test\WebTestCase;
use Wescape\CoreBundle\Validator\Constraint\ClientExists;

class UserControllerTest extends WebTestCase
{
    /** @var Client */
    private $client;

    protected function setUp() {
        parent::setUp();
        $this->recreateDatabase();
        $this->loadFixtures([LoadOAuthUsersTests::class, LoadOAuthClientTests::class]);
        $this->client = self::createClient();
    }

    public function testGetAction() {

    }

    public function testPostAction() {
        // Email non valida
        $invalidEmailUser = $this->getInvalidEmailUser();
        $this->client->request("POST", "/api/v1/users.json", $invalidEmailUser);
        $this->assertStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client);
        $this->assertContains((new Email())->message, $this->client->getResponse()->getContent());

        // Client id non inviato
        $idNotFoundUser = $this->getIdNotFoundUser();
        $this->client->request("POST", "/api/v1/users.json", $idNotFoundUser);
        $this->assertStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client);
        $this->assertContains((new ClientExists())->idNotFound, $this->client->getResponse()->getContent());

        // Client secret non inviato
        $secretNotFoundUser = $this->getSecretNotFoundUser();
        $this->client->request("POST", "/api/v1/users.json", $secretNotFoundUser);
        $this->assertStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client);
        $this->assertContains((new ClientExists())->secretNotFound, $this->client->getResponse()->getContent());

        // Client id non valido
        $invalidClientIdUsers = $this->getInvalidClientFormatUsers();
        foreach ($invalidClientIdUsers as $invalidClientIdUser) {
            $this->client->request("POST", "/api/v1/users.json", $invalidClientIdUser);
            $this->assertStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client);
            $this->assertContains(
                str_replace("%id%", $invalidClientIdUser['client']['id'],
                    (new ClientExists())->invalidClientIdFormat),
                $this->client->getResponse()->getContent());
        }

        // Client non trovato
        $invalidClientUsers = $this->getInvalidClientUsers();
        foreach ($invalidClientUsers as $invalidClientUser) {
            $this->client->request("POST", "/api/v1/users.json", $invalidClientUser);
            $this->assertStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client);
            $this->assertContains((new ClientExists())->invalidClient, $this->client->getResponse()->getContent());
        }

        // utente valido
        $validUser = $this->getValidUser();
        $this->client->request("POST", "/api/v1/users.json", $validUser);
        $this->assertStatusCode(Response::HTTP_CREATED, $this->client);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($validUser['email'], $responseData['email']);
        $this->assertEquals($validUser['email'], $responseData['email_canonical']);
        $this->assertEquals($validUser['email'], $responseData['username']);
        $this->assertEquals($validUser['email'], $responseData['username_canonical']);
        $this->assertEquals(true, $responseData['enabled']);
        $this->assertEquals(false, $responseData['locked']);
        $this->assertEquals(false, $responseData['expired']);
        $this->assertEquals(false, $responseData['credentials_expired']);
        // Non testare ROLE_USER, non viene salvato nel database
        // https://github.com/FriendsOfSymfony/FOSUserBundle/issues/1102

        // Duplicazione dell'utente
        $this->client->request("POST", "/api/v1/users.json", $validUser);
        $this->assertStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client);
        $this->assertContains("Duplicate entry", $this->client->getResponse()->getContent());
    }

    public function testPutAction() {

    }

    public function testDeleteAction() {

    }

    /**
     * @return array
     */
    private function getValidUser() {
        return [
            "email" => "test@wescape.it",
            "plainPassword" => "test",
            "client" => [
                "id" => "1_" . LoadOAuthClientTests::RANDOM_ID,
                "secret" => LoadOAuthClientTests::SECRET
            ]
        ];
    }

    /**
     * @return array
     */
    private function getInvalidEmailUser() {
        return [
            "email" => "invalidEmail",
            "plainPassword" => "test",
            "client" => [
                "id" => "1_" . LoadOAuthClientTests::RANDOM_ID,
                "secret" => LoadOAuthClientTests::SECRET
            ]
        ];
    }

    /**
     * @return array
     */
    private function getIdNotFoundUser() {
        return [
            "email" => "test@wescape.it",
            "plainPassword" => "test",
            "client" => [
                "secret" => LoadOAuthClientTests::SECRET
            ]
        ];
    }

    /**
     * @return array
     */
    private function getSecretNotFoundUser() {
        return [
            "email" => "test@wescape.it",
            "plainPassword" => "test",
            "client" => [
                "id" => "1_" . LoadOAuthClientTests::RANDOM_ID
            ]
        ];
    }

    /**
     * @return array
     */
    private function getInvalidClientFormatUsers() {
        return [
            [
                "email" => "test@wescape.it",
                "plainPassword" => "test",
                "client" => [
                    "id" => LoadOAuthClientTests::RANDOM_ID,
                    "secret" => LoadOAuthClientTests::SECRET
                ]
            ],
            [
                "email" => "test@wescape.it",
                "plainPassword" => "test",
                "client" => [
                    "id" => "_" . LoadOAuthClientTests::RANDOM_ID,
                    "secret" => LoadOAuthClientTests::SECRET
                ]
            ],
            [
                "email" => "test@wescape.it",
                "plainPassword" => "test",
                "client" => [
                    "id" => "1_2_" . LoadOAuthClientTests::RANDOM_ID,
                    "secret" => LoadOAuthClientTests::SECRET
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getInvalidClientUsers() {
        return [
            [
                "email" => "test@wescape.it",
                "plainPassword" => "test",
                "client" => [
                    "id" => "1_" . LoadOAuthClientTests::RANDOM_ID . "WUTISTHIS",
                    "secret" => LoadOAuthClientTests::SECRET
                ]
            ], [
                "email" => "test@wescape.it",
                "plainPassword" => "test",
                "client" => [
                    "id" => "1_" . LoadOAuthClientTests::RANDOM_ID,
                    "secret" => LoadOAuthClientTests::SECRET . "DAFUQ"
                ]
            ]
        ];
    }
}