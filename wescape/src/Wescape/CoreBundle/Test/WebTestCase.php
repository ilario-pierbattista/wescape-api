<?php

namespace Wescape\CoreBundle\Test;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class WebTestCase extends \Liip\FunctionalTestBundle\Test\WebTestCase
{
    protected function wipeDatabase() {
        $this->runCommand("doctrine:schema:drop", ["--force" => "true", "--env" => "test"]);
        $this->runCommand("doctrine:schema:create", ["--env" => "test"]);
    }

    /**
     * Autentica il client usato per i test come un utente normale
     *
     * @param Client $client
     */
    protected function authenticateUser(Client $client) {
        $session = $client->getContainer()->get('session');

        $firewall = 'api';
        $token = new UsernamePasswordToken('test_user', null, $firewall, array('ROLE_USER'));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * Autentica il client usato per i test come admin
     *
     * @param Client $client
     */
    protected function authenticateAdmin(Client $client) {
        $session = $client->getContainer()->get('session');

        $firewall = 'api';
        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}