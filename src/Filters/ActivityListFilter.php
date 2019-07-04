<?php

namespace App\Filters;

use App\Entity\User;
use App\Repository\UserRepository;

class ActivityListFilter
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var User $owner
     */
    public $owner;

    /**
     * @var integer
     */
    public $assignedUser;

    /**
     * @var array
     */
    public $technology;

    /**
     * @var array
     */
    public $activityType;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function setFilterFields($filter)
    {
        if ($filter) {
            if (array_key_exists('name', $filter)) {
                $this->name = $filter['name'];
            }
            if (array_key_exists('status', $filter)) {
                $this->status = $filter['status'];
            }
            if (array_key_exists('owner', $filter)) {
                $this->owner = $this->userRepository->find($filter['owner']);
            }
            if (array_key_exists('technology', $filter)) {
                $this->technology = $filter['technology'];
            }
            if (array_key_exists('activityType', $filter)) {
                $this->activityType = $filter['activityType'];
            }
            if (array_key_exists('assignedUser', $filter)) {
                $this->assignedUser = $this->userRepository->find($filter['assignedUser']);
            }
        }

        return $this;
    }
}
