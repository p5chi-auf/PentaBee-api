<?php

namespace App\Tests;

use App\DataFixtures\TechnologyFixtures;
use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    use FixturesTrait;

    private $client;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->loadFixtures([
            TechnologyFixtures::class,
            UserFixtures::class,
        ]);
        $this->client = self::createClient();
    }

    private function loginPostRequest($data)
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    public function testSuccessfulLogin()
    {
        $data = [
            'username' => 'ADMIN',
            'password' => 'iamadmin'
        ];
        $this->loginPostRequest($data);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('token', json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testUnsuccessfulLogin()
    {
        $data = [
            'username' => 'incorrectUsername',
            'password' => 'incorrectPassword',
        ];
        $this->loginPostRequest($data);

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertEquals('Bad credentials.', json_decode($responseContent, true)['message']);
    }
}
