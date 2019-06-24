<?php

namespace App\Security;

use App\Entity\Activity;
use App\Entity\User;
use App\Repository\ActivityUserRepository;

class AccessRightsPolicy
{

    /**
     * @var ActivityUserRepository
     */
    private $repository;

    public function __construct(ActivityUserRepository $activityUserRepository)
    {
        $this->repository = $activityUserRepository;
    }

    public function checkRightsToActivity(Activity $activity, User $user): bool
    {
        if (($activity->isPublic() === true) || $user === $activity->getOwner()) {
            return true;
        }

        $userToActivity = $this->repository->findBy(array('activity' => $activity, 'user' => $user));
        return !empty($userToActivity);
    }
}
