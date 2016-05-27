<?php

namespace Wescape\CoreBundle\Test;


use Symfony\Bundle\FrameworkBundle\Client;
use Wescape\CoreBundle\DataFixtures\ORM\LoadOAuthClientTests;

abstract class WebTestCase extends \Liip\FunctionalTestBundle\Test\WebTestCase
{
    /** @var bool Flag per garantire un'unica esecuzione del metodo */
    private static $setupBeforeTestsExecuted = false;

    protected function setUp() {
        parent::setUp();
        if(! self::$setupBeforeTestsExecuted) {
            $this->setUpBeforeTests();
            self::$setupBeforeTestsExecuted = true;
        }
    }

    protected function setUpBeforeTests() {

    }

    protected function recreateDatabase() {
        $this->runCommand("doctrine:schema:drop", ["--force" => "true", "--env" => "test"]);
        $this->runCommand("doctrine:schema:create", ["--env" => "test"]);
    }

    protected function clearTables($tables) {
        if(!is_array($tables)) {
            $tables = [$tables];
        }

        foreach ($tables as $table) {
            $deleteTableSql = /** @lang SQL */ "DELETE FROM '".$table."'";
            $resetAutoIncrementSql = /** @lang SQL*/ "ALTER TABLE '".$table."' AUTO_INCREMENT = 0";
            $this->runCommand("doctrine:query:sql", [
                "sql" =>  $deleteTableSql,
                "--env" => "test"
            ]);
            $this->runCommand("doctrine:query:sql", [
                "sql" => $resetAutoIncrementSql,
                "--env" => "test"
            ]);
        }
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
    
    protected function printJsonContent(Client $client) {
        echo "\n";
        echo json_encode(json_decode($client->getResponse()->getContent()), JSON_PRETTY_PRINT);
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
            "client_id" => "1_" . LoadOAuthClientTests::RANDOM_ID,
            "client_secret" => LoadOAuthClientTests::SECRET,
            "username" => $username,
            "password" => $password
        ]);
        return json_decode($client->getResponse()->getContent(), true);
    }
}