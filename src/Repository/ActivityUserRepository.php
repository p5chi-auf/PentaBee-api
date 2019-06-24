<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\ActivityUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ActivityUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivityUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivityUser[]    findAll()
 * @method ActivityUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ActivityUser::class);
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

    public function hasUserApplied(User $applier, Activity $activity)
    {
        $queryBuilder = $this->createQueryBuilder('activity_user');
        $queryBuilder
            ->select('activity_user.id')
            ->where(
                $queryBuilder->expr()->andX(
                    'activity_user.user = :applier',
                    'activity_user.activity = :activity',
                    'activity_user.type != :type'
                )
            )
            ->setParameter('applier', $applier)
            ->setParameter('activity', $activity)
            ->setParameter('type', ActivityUser::TYPE_INVITED);

        return $queryBuilder->getQuery()->getResult();
    }


    public function getApplicantsForActivity(Activity $activity): array
    {
        $queryBuilder = $this->createQueryBuilder('activity_user');
        $queryBuilder
            ->select('user')
            ->innerJoin(User::class, 'user', Join::WITH, 'activity_user.user = user.id')
            ->where(
                $queryBuilder->expr()->andX(
                    'activity_user.activity = :activity',
                    'activity_user.type = :type'
                )
            )
            ->setParameter('activity', $activity)
            ->setParameter('type', ActivityUser::TYPE_APPLIED);

        return $queryBuilder->getQuery()->getArrayResult();
    }
}
