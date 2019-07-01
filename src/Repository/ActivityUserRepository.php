<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\ActivityUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ActivityUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivityUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivityUser[]    findAll()
 * @method ActivityUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityUserRepository extends ServiceEntityRepository
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(RegistryInterface $registry, UserRepository $userRepository)
    {
        parent::__construct($registry, ActivityUser::class);
        $this->userRepository = $userRepository;
    }

    /**
     * Persist an Activity.
     * @param ActivityUser $activityUser
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(ActivityUser $activityUser): void
    {
        $manager = $this->getEntityManager();
        $manager->persist($activityUser);
        $manager->flush();
    }

    /**
     * Persist an Activity appliance.
     * @param Activity $activity
     * @param User $applier
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function apply(Activity $activity, User $applier): void
    {
        $activityUser = new ActivityUser();
        $activityUser->setActivity($activity);
        $activityUser->setUser($applier);
        $activityUser->setType(ActivityUser::TYPE_APPLIED);
        $this->save($activityUser);
    }

    /**
     * @param User $user
     * @param Activity $activity
     * @return ActivityUser|null
     * @throws NonUniqueResultException
     */
    public function getActivityUser(User $user, Activity $activity): ?ActivityUser
    {
        $queryBuilder = $this->createQueryBuilder('activity_user');
        $queryBuilder
            ->select('activity_user')
            ->where(
                $queryBuilder->expr()->andX(
                    'activity_user.user = :user',
                    'activity_user.activity = :activity'
                )
            )
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('activity', $activity);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getApplicantsForActivityPaginated(
        Activity $activity,
        int $currentPage = 1,
        int $pageSize = ActivityUser::NUM_APPLICANTS_PER_PAGE
    ): Query {
        $queryBuilder = $this->userRepository->getApplicantsForActivity($activity);

        $currentPage = $currentPage < 1 ? 1 : $currentPage;
        $firstResult = ($currentPage - 1) * $pageSize;

        $query = $queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($pageSize)
            ->getQuery();

        return $query;
    }

    /**
     * Persist an Activity invitation.
     * @param Activity $activity
     * @param User $invitedUser
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function invite(Activity $activity, User $invitedUser): void
    {
        $activityUser = new ActivityUser();
        $activityUser->setActivity($activity);
        $activityUser->setUser($invitedUser);
        $activityUser->setType(ActivityUser::TYPE_INVITED);
        $this->save($activityUser);
    }
}
