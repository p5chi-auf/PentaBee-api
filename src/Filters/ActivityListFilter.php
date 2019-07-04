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
     * @var integer $owner
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

    public function setFilterFields(array $filter): self
    {
        if (!empty($filter['name'])) {
            $this->name = $filter['name'];
        }
        if (!empty($filter['status'])) {
            $this->status = $filter['status'];
        }
        if (!empty($filter['owner'])) {
            $this->owner = $filter['owner'];
        }
        if (!empty($filter['technology'])) {
            $this->technology = $filter['technology'];
        }
        if (!empty($filter['activityType'])) {
            $this->activityType = $filter['activityType'];
        }
        if (!empty($filter['assignedUser'])) {
            $this->assignedUser = $filter['assignedUser'];
        }

        return $this;
    }
}
