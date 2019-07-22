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
    private $directoryManager;

    public function __construct(
        RegistryInterface $registry,
        UserRepository $userRepository,
        UserAvatarManager $directoryManager
    ) {
        parent::__construct($registry, Image::class);
        $this->userRepository = $userRepository;
        $this->directoryManager = $directoryManager;
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
        $image = new Image();
        $filename = $user->getId() . '.' . $uploadedFile->guessExtension();

        $this->directoryManager->saveAvatarInDirectory($uploadedFile, $user);
        $image->setFile($filename);
        $image->setAlt('User avatar');
        $entityManager = $this->getEntityManager();
        $entityManager->persist($image);
        $entityManager->flush();

        $this->userRepository->saveAvatar($user, $image);
    }

    /**
     * @param User $user
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeUserAvatar(User $user): void
    {
        $image = $user->getAvatar();

        $this->directoryManager->removeAvatarFromDirectory($user);

        $user->setAvatar(null);
        $entityManager = $this->getEntityManager();
        $entityManager->remove($image);
        $entityManager->flush();
    }
}
