<?php

namespace App\Transformer;

use App\Base64EncodedFileTransformers\Base64EncodedFile;
use App\Base64EncodedFileTransformers\UploadedBase64EncodedFile;
use App\DTO\UserDTO;
use App\Entity\Technology;
use App\Entity\User;
use App\Exceptions\DuplicateUsernameEmail;
use App\Exceptions\EntityNotFound;
use App\Exceptions\NotValidOldPassword;
use App\Repository\ImageRepository;
use App\Repository\TechnologyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ImageRepository
     */
    private $imageRepository;

    /**
     * UserTransformer constructor.
     * @param TechnologyRepository $techRepo
     * @param UserPasswordEncoderInterface $encoder
     * @param UserRepository $userRepository
     * @param ImageRepository $imageRepository
     */
    public function __construct(
        TechnologyRepository $techRepo,
        UserPasswordEncoderInterface $encoder,
        UserRepository $userRepository,
        ImageRepository $imageRepository
    ) {
        $this->techRepo = $techRepo;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
        $this->imageRepository = $imageRepository;
    }

    /**
     * @param UserDTO $dto
     * @return User
     * @throws DuplicateUsernameEmail
     */
    public function registerTransform(
        UserDTO $dto
    ): User {
        $entity = new User();
        if ($this->userRepository->findOneBy(array('username' => $dto->username)) ||
            $this->userRepository->findOneBy(array('email' => $dto->email))) {
            $duplicateUsernameEmail = new DuplicateUsernameEmail(
                'Username or Email already exist',
                400
            );
            throw $duplicateUsernameEmail;
        }
        {
            $entity->setName($dto->name);
        }
        $entity->setPassword($this->encoder->encodePassword($entity, $dto->password));
        $entity->setSurname($dto->surname);
        $entity->setUsername($dto->username);
        $entity->setEmail($dto->email);
        return $entity;
    }

    /**
     * @param UserDTO $dto
     * @param User $user
     * @return User
     * @throws EntityNotFound
     * @throws ORMException
     * @throws OptimisticLockException
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
        $user->setPosition($dto->position);
        $user->setSeniority($dto->seniority);
        $user->setBiography($dto->biography);
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

        if (!empty($dto->avatar)) {
            $userAvatar = new UploadedBase64EncodedFile(new Base64EncodedFile($dto->avatar));

            if ($user->getAvatar()) {
                $this->imageRepository->removeUserAvatar($user);
            }

            $this->imageRepository->uploadUserAvatar($userAvatar, $user);
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
                'Old password not valid.',
                400
            );
            throw $passwordDoNotMatch;
        }
        $user->setPassword($this->encoder->encodePassword($user, $dto->password));
        return $user;
    }
}
