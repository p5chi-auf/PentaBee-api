<?php

namespace App\Repository;

use App\Entity\Feedback;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Feedback|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feedback|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feedback[]    findAll()
 * @method Feedback[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    /**
     * Persist a Feedback.
     * @param Feedback $feedback
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Feedback $feedback): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($feedback);
        $entityManager->flush();
    }

    public function countFeedback(User $user): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('feedback');
        $queryBuilder->select('COUNT (feedback)')
            ->where('feedback.userTo = :user')
            ->setParameter('user', $user);

        return $queryBuilder;
    }
}
