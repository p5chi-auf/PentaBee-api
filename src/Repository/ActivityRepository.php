<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * @param Activity $activity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Activity $activity): void
    {
        $em = $this->getEntityManager();
        $em->remove($activity);
        $em->flush();
    }

    /**
     * Persist an Activity.
     * @param Activity $newActivity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Activity $newActivity): void
    {
        $em = $this->getEntityManager();
        $em->persist($newActivity);
        $em->flush();
    }
}
