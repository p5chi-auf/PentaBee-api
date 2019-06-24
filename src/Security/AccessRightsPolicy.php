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

        if (($activity->isPublic() === false) && $user !== $activity->getOwner()) {
            $userToActivity = $this->repository->findBy(array('activity' => $activity, 'user' => $user));
            if (empty($userToActivity)) {
                return false;
            }
        }
        return true;
    }
}
