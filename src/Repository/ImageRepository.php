<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    private $userTargetDirectory;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct($userTargetDirectory, RegistryInterface $registry, UserRepository $userRepository)
    {
        parent::__construct($registry, Image::class);
        $this->userTargetDirectory = $userTargetDirectory;
        $this->userRepository = $userRepository;
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

        $uploadedFile->move($this->userTargetDirectory, $filename);

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

        $filename = $image->getFile();
        $filepath = $this->userTargetDirectory . $filename;

        $user->setAvatar(null);

        $entityManager = $this->getEntityManager();
        $entityManager->remove($image);
        $entityManager->flush();

        $filesystem = new Filesystem();
        $filesystem->remove($filepath);
    }
}
