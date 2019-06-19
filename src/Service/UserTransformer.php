<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\Technology;
use App\Entity\User;
use App\Exceptions\EntityNotFound;
use App\Exceptions\NotValidOldPassword;
use App\Repository\TechnologyRepository;
use DateTime;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserTransformer
{
    /**
     * @var TechnologyRepository
     */
    private $techRepo;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * UserTransformer constructor.
     * @param TechnologyRepository $techRepo
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        TechnologyRepository $techRepo,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->techRepo = $techRepo;
        $this->encoder = $encoder;
    }

    /**
     * @param UserDTO $dto
     * @return User
     * @throws Exception
     */
    public function registerTransform(
        UserDTO $dto
    ): User {
        $entity = new User();
        $entity->setName($dto->name);
        $entity->setPassword($this->encoder->encodePassword($entity, $dto->password));
        $entity->setSurname($dto->surname);
        $entity->setUsername($dto->username);
        $entity->setCreatedAt(new DateTime('now'));
        $entity->setUpdatedAt(new DateTime('now'));
        $entity->setEmail($dto->email);
        return $entity;
    }

    /**
     * @param UserDTO $dto
     * @param User $user
     * @return User
     * @throws EntityNotFound
     */
    public function editTransform(
        UserDTO $dto,
        User $user
    ): User {
        foreach ($user->getTechnologies() as $techToRemove) {
            $user->removeTechnology($techToRemove);
        }

        $user->setName($dto->name);
        $user->setSurname($dto->surname);
        $user->setUsername($dto->username);
        $user->setPosition($dto->position);
        $user->setSeniority($dto->seniority);
        $user->setUpdatedAt(new DateTime('now'));
        $user->setEmail($dto->email);

        if ($dto->technologies !== null) {
            foreach ($dto->technologies as $tech) {
                $techID = $tech->id;
                $techToAdd = $this->techRepo->find($techID);
                if (!$techToAdd) {
                    $entityNotFound = new EntityNotFound(
                        Technology::class,
                        $techID,
                        'No technology found.'
                    );
                    throw $entityNotFound;
                }
                $user->addTechnology($techToAdd);
            }
        }
        return $user;
    }

    /**
     * @param UserDTO $dto
     * @param User $user
     * @return User
     * @throws NotValidOldPassword
     */
    public function changePasswordTransform(
        UserDTO $dto,
        User $user
    ): User {
        $match = $this->encoder->isPasswordValid($user, $dto->oldPassword);
        if (!$match) {
            $passwordDoNotMatch = new NotValidOldPassword(
                'Old password not valid.'
            );
            throw $passwordDoNotMatch;
        }
        $user->setPassword($this->encoder->encodePassword($user, $dto->password));
        $user->setUpdatedAt(new DateTime('now'));
        return $user;
    }
}
