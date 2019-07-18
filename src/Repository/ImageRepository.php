<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;
use App\Service\ImageCropperResizer;
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
    /**
     * @var ImageCropperResizer
     */
    private $cropperResizer;

    public function __construct(
        $userTargetDirectory,
        RegistryInterface $registry,
        UserRepository $userRepository,
        ImageCropperResizer $cropperResizer
    ) {
        parent::__construct($registry, Image::class);
        $this->userTargetDirectory = $userTargetDirectory;
        $this->userRepository = $userRepository;
        $this->cropperResizer = $cropperResizer;
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

        $resizedImage256 = $this->cropperResizer->avatarCropResize($uploadedFile, 256);
        $resizedImage256->move($this->userTargetDirectory . '256x256/', $filename);

        $resizedImage40 = $this->cropperResizer->avatarCropResize($uploadedFile, 40);
        $resizedImage40->move($this->userTargetDirectory . '40x40/', $filename);

        $uploadedFile->move($this->userTargetDirectory . 'Original/', $filename);

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
        $filepaths = array(
            [
                'original' => $this->userTargetDirectory . 'Original/' . $filename,
                '256x256' => $this->userTargetDirectory . '256x256/' . $filename,
                '40x40' => $this->userTargetDirectory . '40x40/' . $filename
            ]
        );

        $user->setAvatar(null);

        $entityManager = $this->getEntityManager();
        $entityManager->remove($image);
        $entityManager->flush();

        $filesystem = new Filesystem();
        foreach ($filepaths as $filepath) {
            $filesystem->remove($filepath);
        }
    }
}
