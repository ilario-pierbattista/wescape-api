<?php
/**
 * Created by PhpStorm.
 * User: ilario
 * Date: 19/04/16
 * Time: 20.08
 */

namespace Wescape\StaticBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MapControllerTest extends WebTestCase
{
    public function testGetMap() {
        $client = static::createClient();

        $client->request("GET", "/static/maps/145");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals("image/jpg",
            $client->getResponse()->headers->get("Content-Type"));

        $client->request("GET", "/static/maps/150");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals("image/jpg",
            $client->getResponse()->headers->get("Content-Type"));

        $client->request("GET", "/static/maps/155");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals("image/jpg",
            $client->getResponse()->headers->get("Content-Type"));

        $client->request("GET", "/static/maps/0");
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request("GET", "/static/maps/12");
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}