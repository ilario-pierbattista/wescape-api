<?php

namespace Wescape\CoreBundle\Test;


use Symfony\Bundle\FrameworkBundle\Client;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthClient;

class WebTestCase extends \Liip\FunctionalTestBundle\Test\WebTestCase
{
    protected function wipeDatabase() {
        $this->runCommand("doctrine:schema:drop", ["--force" => "true", "--env" => "test"]);
        $this->runCommand("doctrine:schema:create", ["--env" => "test"]);
    }

    /**
     * Autentica il client usato per i test come un utente normale
     *
     * @return Client
     */
    protected function getAuthenticatedUser() {
        $tokenClient = self::createClient();
        $response = $this->getOAuthTokens($tokenClient, "user", "user");
        return self::createClient([], [
            "HTTP_Authorization" => "Bearer " . $response['access_token']
        ]);
    }

    /**
     * Autentica il client usato per i test come admin
     *
     * @return Client
     */
    protected function getAuthenticatedAdmin() {
        $tokenClient = self::createClient();
        $response = $this->getOAuthTokens($tokenClient, "admin", "admin");
        return self::createClient([], [
            "HTTP_Authorization" => "Bearer " . $response['access_token']
        ]);
    }

    /**
     * Richiede l'access token inviando email e password
     *
     * @param Client $client
     * @param        $username
     * @param        $password
     *
     * @return mixed
     */
    private function getOAuthTokens(Client $client, $username, $password) {
        $client->request("POST", "/oauth/v2/token", [
            "grant_type" => "password",
            "client_id" => "1_" . LoadOAuthClient::RANDOM_ID,
            "client_secret" => LoadOAuthClient::SECRET,
            "username" => $username,
            "password" => $password
        ]);
        return json_decode($client->getResponse()->getContent(), true);
    }
}