<?php

namespace App\Repository;

use App\Entity\UserTechnology;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserTechnology|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTechnology|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTechnology[]    findAll()
 * @method UserTechnology[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTechnologyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserTechnology::class);
    }

    // /**
    //  * @return UserTechnology[] Returns an array of UserTechnology objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserTechnology
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
