<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;

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

    public function __construct(
        RegistryInterface $registry,
        UserRepository $userRepository
    ) {
        parent::__construct($registry, Image::class);
        $this->userRepository = $userRepository;
    }

    /**
     * @param Image $image
     * @param User $user
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function uploadUserAvatar(
        Image $image,
        User $user
    ): void {
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
        $user->setAvatar(null);

        $entityManager = $this->getEntityManager();
        $entityManager->remove($image);
        $entityManager->flush();
    }
}
