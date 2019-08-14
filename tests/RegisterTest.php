<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterTest extends WebTestCase
{
    use FixturesTrait;

    /** @var EntityManager */
    private $entityManager;

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

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        $repository = $this->entityManager->getRepository(User::class);

        $users = $repository->findBy(array(
            'username' => 'TestUsername',
            'email' => 'testemail@test.mail',
            'name' => 'TestName',
            'surname' => 'TestSurname'
        ));

        $this->assertCount(1, $users);

        /** @var User $user */
        $user = $repository->findOneBy(array(
            'username' => 'TestUsername',
            'email' => 'testemail@test.mail',
            'name' => 'TestName',
            'surname' => 'TestSurname'
        ));

        $this->assertEquals('TestUsername', $user->getUsername());
        $this->assertEquals('testemail@test.mail', $user->getEmail());
        $this->assertEquals('TestName', $user->getName());
        $this->assertEquals('TestSurname', $user->getSurname());
    }
}
