<?php

namespace App\Tests;

use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterTest extends WebTestCase
{
    use FixturesTrait;

    public function testSuccessfullRegister()
    {
        $this->loadFixtures();

        $client = self::createClient();

        $data = [
            'username' => 'TestUsername',
            'password' => 'TestPassword_1',
            'confirm_password' => 'TestPassword_1',
            'email' => 'testemail@test.mail',
            'name' => 'TestName',
            'surname' => 'TestSurname'
        ];

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
