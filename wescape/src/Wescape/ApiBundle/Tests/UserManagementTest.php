<?php

namespace Wescape\ApiBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\Tests\LazyProxy\Instantiator\RealServiceInstantiatorTest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthClientTests;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthUsersTests;
use Wescape\CoreBundle\DataFixtures\ORM\PasswordResetUsersTest;
use Wescape\CoreBundle\Service\ErrorCodes;
use Wescape\CoreBundle\Service\PasswordResetService;
use Wescape\CoreBundle\Test\WebTestCase;

class UserManagementTest extends WebTestCase
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
            PasswordResetUsersTest::class
        ]);
        $this->client = self::createClient();
    }

    public function testRequestPassword() {
        $notExistingUser = $this->getNotExistsingUser();
        $existingUser = $this->getExistingUser();

        // Utente non esistente
        $this->client->request("POST", "/api/v1/users/password/request", $notExistingUser);
        $this->assertStatusCode(ErrorCodes::PASSWORD_RESET_WRONG_EMAIL, $this->client);
        $this->assertContains(PasswordResetService::USER_NOT_FOUND_MSG,
            $this->client->getResponse()->getContent());

        // Utente esistente
        $this->client->request("POST", "/api/v1/users/password/request", $existingUser);
        $this->assertStatusCode(Response::HTTP_ACCEPTED, $this->client);
    }

    public function testResetPassword() {
        $notExistingUser = $this->getNotExistsingUser();

        $this->client->request("POST", "/api/v1/users/password/reset", $notExistingUser);
        $this->assertStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client);
        $this->assertContains((new NotBlank())->message,
            $this->client->getResponse()->getContent());

        $notExistingUser['reset_password_token'] = "unemptytoken";
        $this->client->request("POST", "/api/v1/users/password/reset", $notExistingUser);
        $this->assertStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client);
        $this->assertContains((new NotBlank())->message,
            $this->client->getResponse()->getContent());

        $notExistingUser['new_password'] = "unemptypassword";
        $this->client->request("POST", "/api/v1/users/password/reset", $notExistingUser);
        $this->assertStatusCode(ErrorCodes::PASSWORD_RESET_WRONG_EMAIL, $this->client);
        $this->assertContains(PasswordResetService::USER_NOT_FOUND_MSG,
            $this->client->getResponse()->getContent());

        $expiredUser = [
            "email" => PasswordResetUsersTest::TEST_EXPIRED_EMAIL,
            "client" => $this->client_data,
            "new_password" => "newpass",
            "reset_password_token" => "invalid"
        ];
        $this->client->request("POST", "/api/v1/users/password/reset", $expiredUser);
        $this->assertStatusCode(ErrorCodes::PASSWORD_RESET_WRONG_SECRET_CODE, $this->client);
        $this->assertContains(PasswordResetService::INVALID_SECRET_TOKEN_MSG,
            $this->client->getResponse()->getContent());

        $expiredUser["reset_password_token"] = PasswordResetUsersTest::TEST_VALID_TOKEN;
        $this->client->request("POST", "/api/v1/users/password/reset", $expiredUser);
        $this->assertStatusCode(ErrorCodes::PASSWORD_RESET_EXPIRED_SECRET, $this->client);
        $this->assertContains(PasswordResetService::EXPIRED_SECRET_TOKEN_MSG,
            $this->client->getResponse()->getContent());

        $validUser = [
            "email" => PasswordResetUsersTest::TEST_VALID_EMAIL,
            "client" => $this->client_data,
            "new_password" => "newpass",
            "reset_password_token" => PasswordResetUsersTest::TEST_VALID_TOKEN
        ];
        $this->client->request("POST", "/api/v1/users/password/reset", $validUser);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testWhoamiAction() {
        $this->client->request('GET', '/api/v1/user/whoami');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $this->client);

        $this->client = $this->getAuthenticatedUser();
        $this->client->request('GET', '/api/v1/user/whoami');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client = $this->getAuthenticatedAdmin();
        $this->client->request('GET', '/api/v1/user/whoami');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    /**
     * @return array
     */
    private function getNotExistsingUser() {
        return [
            "email" => "not_existing_email@wescape.it",
            "client" => $this->client_data
        ];
    }

    /**
     * @return array
     */
    private function getExistingUser() {
        return [
            "email" => "test2@wescape.it",
            "client" => $this->client_data
        ];
    }
}