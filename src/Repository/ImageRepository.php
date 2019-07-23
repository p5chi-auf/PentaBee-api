<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;
use App\Service\UserAvatarManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserAvatarManager
     */
    private $userAvatarManager;

    public function __construct(
        RegistryInterface $registry,
        UserRepository $userRepository,
        UserAvatarManager $userAvatarManager
    ) {
        parent::__construct($registry, Image::class);
        $this->userRepository = $userRepository;
        $this->userAvatarManager = $userAvatarManager;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param User $user
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function uploadUserAvatar(
        UploadedFile $uploadedFile,
        User $user
    ): void {
        $image = $this->userAvatarManager->createImage($uploadedFile, $user);
        $this->userAvatarManager->saveAvatarInDirectory($uploadedFile, $user);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($image);
        $entityManager->flush();

        $user->setAvatar($image);
        $this->userRepository->save($user);
    }

    /**
     * @param User $user
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeUserAvatar(User $user): void
    {
        $image = $user->getAvatar();

        $this->userAvatarManager->removeAvatarFromDirectory($user);

        $user->setAvatar(null);
        $entityManager = $this->getEntityManager();
        $entityManager->remove($image);
        $entityManager->flush();
    }
}
