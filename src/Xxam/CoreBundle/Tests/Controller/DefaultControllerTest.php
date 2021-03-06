<?php

namespace Xxam\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue(
            $client->getResponse()->isRedirect()
        );

       $this->assertTrue($crawler->filter('body')->count() > 0);
    }
}
