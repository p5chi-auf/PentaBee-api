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

    public function __construct(ActivityUserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function checkRightsToActivity(Activity $activity, User $user): bool
    {
        $userToActivity = $this->repository->findBy(array('activity' => $activity, 'user' => $user));

        if (empty($userToActivity) && ($activity->isPublic() === false) && $user !== $activity->getOwner()) {
            return false;
        }
        return true;
    }
}