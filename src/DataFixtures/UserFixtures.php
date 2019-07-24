<?php

namespace App\DataFixtures;

use App\Entity\Technology;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public const POSITIONS = [
        'dev',
        'po',
        'qa'
    ];

    public const LOCATION = [
        'CHI',
        'NYC',
        'BOS',
        'FRA',
        'PAR',
        'ORL',
        'BUC',
        'BRA',
        'CLU',
        'IAS',
        'HAN',
        'GUA',
        'LYO'
    ];

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $user = new User();
        $user->setUsername('ADMIN');
        $user->setPassword($this->encoder->encodePassword($user, 'iamadmin'));
        $user->setPosition(self::POSITIONS[rand(0, 2)]);
        $user->setSeniority(1);
        $user->setLocation(self::LOCATION[rand(0, 12)]);
        $user->setName('Staci');
        $user->setSurname('Nicolae');
        $user->setEmail('nstaci@pentalog.com');
        $user->setBiography($faker->sentence);
        $manager->persist($user);

        $user = new User();
        $user->setUsername('USER');
        $user->setPassword($this->encoder->encodePassword($user, 'passtester'));
        $user->setPosition(self::POSITIONS[rand(0, 2)]);
        $user->setSeniority(1);
        $user->setLocation(self::LOCATION[rand(0, 12)]);
        $user->setName('Druta');
        $user->setSurname('Mihai');
        $user->setEmail('mdruta@pentalog.com');
        $user->setBiography($faker->sentence);
        $manager->persist($user);

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setUsername($faker->userName);
            $user->setPassword($this->encoder->encodePassword($user, 'test_Password1'));
            $user->setPosition(self::POSITIONS[rand(0, 2)]);
            $user->setSeniority(mt_rand(0, 2));
            $user->setLocation(self::LOCATION[rand(0, 12)]);
            $user->setName($faker->firstName);
            $user->setSurname($faker->lastName);
            $user->setEmail($faker->email);
            $user->setBiography($faker->sentence);
            $this->setReference('user_' . $i, $user);

            /** @var Technology $technology */
            $technology = $this->getReference('tech_' . array_rand(TechnologyFixtures::TECHNOLOGIES));
            $user->addTechnology($technology);
            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return array(
            TechnologyFixtures::class,
        );
    }
}
