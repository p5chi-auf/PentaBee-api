<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\Technology;
use App\Entity\User;
use App\Exceptions\EntityNotFound;
use App\Exceptions\NotValidOldPassword;
use App\Repository\TechnologyRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserTransformer
{
    /**
     * @var UserRepository
     */
    private $userRepo;
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
     * @param UserRepository $userRepo
     * @param TechnologyRepository $techRepo
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        UserRepository $userRepo,
        TechnologyRepository $techRepo,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->userRepo = $userRepo;
        $this->techRepo = $techRepo;
        $this->encoder = $encoder;
    }
    /**
     * @param UserDTO $dto
     * @return User
     * @throws EntityNotFound
     */
    public function registerTransform(
        UserDTO $dto
    ): User {
        $entity = new User();
        $entity->setName($dto->name);
        $entity->setPassword($this->encoder->encodePassword($entity, $dto->password));
        $entity->setSurname($dto->surname);
        $entity->setUsername($dto->username);
        $entity->setCreatedAt($dto->createdAt);
        $entity->setUpdatedAt($dto->updatedAt);
        $entity->setEmail($dto->email);
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
                $entity->addTechnology($techToAdd);
            }
        }
        return $entity;
    }

    /**
     * @param UserDTO $dto
     * @return User
     * @throws EntityNotFound
     */
    public function editTransform(
        UserDTO $dto
    ): User {
        $entity = $this->userRepo->find($dto->id);
        if (!$entity) {
            $entityNotFound = new EntityNotFound(
                User::class,
                $dto->id,
                'No user found.'
            );
            throw $entityNotFound;
        }
        foreach ($entity->getTechnologies() as $techToRemove) {
            $entity->removeTechnology($techToRemove);
        }
        $entity->setName($dto->name);
        $entity->setSurname($dto->surname);
        $entity->setUsername($dto->username);
        $entity->setPosition($dto->position);
        $entity->setSeniority($dto->seniority);
        $entity->setCreatedAt($dto->createdAt);
        $entity->setUpdatedAt($dto->updatedAt);
        $entity->setEmail($dto->email);
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
                $entity->addTechnology($techToAdd);
            }
        }
        return $entity;
    }

    /**
     * @param UserDTO $dto
     * @return User
     * @throws NotValidOldPassword
     * @throws EntityNotFound
     */
    public function changePasswordTransform(
        UserDTO $dto
    ): User {
        $entity = $this->userRepo->find($dto->id);
        if (!$entity) {
            $entityNotFound = new EntityNotFound(
                User::class,
                $dto->id,
                'No user found.'
            );
            throw $entityNotFound;
        }
        $match = $this->encoder->isPasswordValid($entity, $dto->password);
        if (!$match) {
            $passwordDoNotMatch = new NotValidOldPassword(
                'Old password not valid.'
            );
            throw $passwordDoNotMatch;
        }
        $entity->setPassword($this->encoder->encodePassword($entity, $dto->newPassword));
        return $entity;
    }
}