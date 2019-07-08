<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\ActivityUser;
use App\Entity\User;
use App\Filters\ActivityListFilter;
use App\Filters\ActivityListPagination;
use App\Filters\ActivityListSort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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
     * @param Activity $activity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Activity $activity): void
    {
        $em = $this->getEntityManager();
        $em->persist($activity);
        $em->flush();
    }

    public function getAvailableActivities(
        ActivityListSort $activityListSort,
        ActivityListFilter $activityListFilter,
        User $user
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('activity');
        $queryBuilder
            ->select('DISTINCT activity')
            ->leftJoin('activity.activityUsers', 'activityUsers')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('activity.public', 1),
                $queryBuilder->expr()->eq('activity.owner', ':user'),
                $queryBuilder->expr()->eq('activityUsers.user', ':user')
            ))
            ->setParameter('user', $user);
        if ($activityListFilter->name !== null) {
            $queryBuilder->andWhere('activity.name LIKE :nameFilter')
                ->setParameter('nameFilter', $activityListFilter->name . '%');
        }

        if ($activityListFilter->status !== null) {
            $queryBuilder->andWhere('activity.status = :statusFilter')
                ->setParameter('statusFilter', $activityListFilter->status);
        }

        if ($activityListFilter->owner !== null) {
            $queryBuilder->andWhere('activity.owner = :ownerFilter')
                ->setParameter('ownerFilter', $activityListFilter->owner);
        }

        if ($activityListFilter->technology !== null) {
            $queryBuilder->join('activity.technologies', 'technology')
                ->andWhere('technology IN (:technologyFilter)')
                ->setParameter('technologyFilter', $activityListFilter->technology);
        }
        if ($activityListFilter->activityType !== null) {
            $queryBuilder->join('activity.types', 'activityType')
                ->andWhere('activityType IN (:activityTypeFilter)')
                ->setParameter('activityTypeFilter', $activityListFilter->activityType);
        }
        if ($activityListFilter->assignedUser !== null) {
            $queryBuilder->leftJoin('activity.activityUsers', 'activityUsersAssigned')
                ->andWhere('activityUsersAssigned.user = :assignedUser')
                ->andWhere('activityUsersAssigned.type = :type')
                ->setParameter('assignedUser', $activityListFilter->assignedUser)
                ->setParameter('type', ActivityUser::TYPE_ASSIGNED);
        }
        if ($activityListSort->name !== null) {
            $queryBuilder->orderBy('activity.name', $activityListSort->name);
        }
        if ($activityListSort->createdAt !== null) {
            $queryBuilder->orderBy('activity.createdAt', $activityListSort->createdAt);
        }
        if ($activityListSort->finalDeadline !== null) {
            $queryBuilder->orderBy('activity.finalDeadline', $activityListSort->finalDeadline);
        }

        return $queryBuilder;
    }

    public function getPaginatedActivities(
        ActivityListPagination $activityListPagination,
        ActivityListSort $activityListSort,
        ActivityListFilter $activityListFilter,
        User $user
    ): Query {
        $queryBuilder = $this->getAvailableActivities($activityListSort, $activityListFilter, $user);

        $currentPage = $activityListPagination->currentPage < 1 ? 1 : $activityListPagination->currentPage;
        $firstResult = ($currentPage - 1) * $activityListPagination->pageSize;

        $query = $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($activityListPagination->pageSize)
            ->getQuery();

        return $query;
    }
}
