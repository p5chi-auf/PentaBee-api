<?php

namespace App\Repository;

use App\Entity\ActivityTechnology;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ActivityTechnology|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivityTechnology|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivityTechnology[]    findAll()
 * @method ActivityTechnology[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityTechnologyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ActivityTechnology::class);
    }

    // /**
    //  * @return ActivityTechnology[] Returns an array of ActivityTechnology objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ActivityTechnology
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
