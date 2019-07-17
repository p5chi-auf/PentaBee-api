<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\ActivityUser;
use App\Entity\Image;
use App\Entity\User;
use App\Filters\ApplicantsListSort;
use App\Filters\UserListFilter;
use App\Filters\UserListPagination;
use App\Filters\UserListSort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Create an User.
     * @param User $newUser
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(User $newUser): void
    {
        $em = $this->getEntityManager();
        $em->persist($newUser);
        $em->flush();
    }

    /**
     * @param User $user
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(User $user): void
    {
        $em = $this->getEntityManager();
        $em->remove($user);
        $em->flush();
    }

    public function getApplicantsForActivity(ApplicantsListSort $applicantsListSort, Activity $activity): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('user');
        $queryBuilder
            ->select('user')
            ->leftJoin('user.activityUsers', 'activityUsers')
            ->where('activityUsers.activity = :activity')
            ->andWhere('activityUsers.type = :type')
            ->setParameter('activity', $activity)
            ->setParameter('type', ActivityUser::TYPE_APPLIED);
        if ($applicantsListSort->seniority !== null) {
            $queryBuilder->orderBy('user.seniority', $applicantsListSort->seniority);
        }

        return $queryBuilder;
    }

    /**
     * @param UserListFilter $userListFilter
     * @param UserListSort $userListSort
     * @return QueryBuilder
     */
    public function getUserList(
        UserListFilter $userListFilter,
        UserListSort $userListSort
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('user');
        $queryBuilder
            ->select('user');
        if (!empty($userListFilter->technology)) {
            $queryBuilder->join('user.technologies', 'technology')
                ->andWhere('technology IN (:technologyFilter)')
                ->setParameter('technologyFilter', $userListFilter->technology);
        }
        if ($userListSort->seniority !== null) {
            $queryBuilder->orderBy('user.seniority', $userListSort->seniority);
        }
        return $queryBuilder;
    }

    public function getPaginatedUserList(
        UserListPagination $userListPagination,
        UserListSort $userListSort,
        UserListFilter $userListFilter
    ): Query {
        $queryBuilder = $this->getUserList($userListFilter, $userListSort);
        if ($userListPagination->pageSize === -1) {
            return $queryBuilder->getQuery();
        }
        $currentPage = $userListPagination->currentPage < 1 ? 1 : $userListPagination->currentPage;
        $firstResult = ($currentPage - 1) * $userListPagination->pageSize;

        $query = $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($userListPagination->pageSize)
            ->getQuery();

        return $query;
    }

    /**
     * @param User $user
     * @param Image $avatar
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveAvatar(User $user, Image $avatar): void
    {
        $user->setAvatar($avatar);
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
    }
}
