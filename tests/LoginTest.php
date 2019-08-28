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
        $this->client = self::createClient();
    }

    private function loadFixturesForTest(): void
    {
        $this->loadFixtures([
            TechnologyFixtures::class,
            UserFixtures::class,
        ]);
    }

    private function loginPostRequest($data): void
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

    private function tokenDecode(string $token): array
    {
        $tokenParts = explode('.', $token);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        return [
            'header' => (array)$jwtHeader,
            'payload' => (array)$jwtPayload,
        ];
    }

    private function getUsernameRoles($token): array
    {
        $decodedToken = $this->tokenDecode($token);
        return [
            'username' => $decodedToken['payload']['username'],
            'roles' => $decodedToken['payload']['roles'][0],
        ];
    }

    public function testSuccessfulAdminLogin(): void
    {
        $this->loadFixturesForTest();
        $data = [
            'username' => 'ADMIN',
            'password' => 'iamadmin'
        ];
        $this->loginPostRequest($data);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('token', json_decode($this->client->getResponse()->getContent(), true));
        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];
        $this->assertEquals($data['username'], $this->getUsernameRoles($token)['username']);
        $this->assertEquals('ROLE_ADMIN', $this->getUsernameRoles($token)['roles']);
    }

    public function testSuccessfulUserLogin(): void
    {
        $this->loadFixturesForTest();
        $data = [
            'username' => 'USER',
            'password' => 'passtester'
        ];
        $this->loginPostRequest($data);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('token', json_decode($this->client->getResponse()->getContent(), true));
        $token = json_decode($this->client->getResponse()->getContent(), true)['token'];
        $this->assertEquals($data['username'], $this->getUsernameRoles($token)['username']);
        $this->assertEquals('ROLE_USER', $this->getUsernameRoles($token)['roles']);
    }

    public function testUnsuccessfulLogin(): void
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
