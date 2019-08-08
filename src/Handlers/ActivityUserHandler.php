<?php

namespace App\Handlers;

use App\Entity\Activity;
use App\Filters\AssignedUsersListPagination;
use App\Repository\ActivityUserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class ActivityUserHandler
{

    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var ActivityUserRepository
     */
    private $activityUserRepository;

    public function __construct(SerializerInterface $serializer, ActivityUserRepository $activityUserRepository)
    {

        $this->serializer = $serializer;
        $this->activityUserRepository = $activityUserRepository;
    }

    public function getAssignedUsersPaginated(
        AssignedUsersListPagination $assignedUsersListPagination,
        Activity $activity
    ): array {
        $paginatedResults = $this->activityUserRepository
            ->getAssignedUsersForActivityPaginated($assignedUsersListPagination, $activity);

        $paginator = new Paginator($paginatedResults);
        $numResults = $paginator->count();

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('UserList'));

        $json = $this->serializer->serialize(
            $paginatedResults->getResult(),
            'json',
            $context
        );

        return array(
            'results' => json_decode($json, true),
            'currentPage' => $assignedUsersListPagination->currentPage,
            'numResults' => $numResults,
            'perPage' => $assignedUsersListPagination->pageSize,
            'numPages' => (int)ceil($numResults / $assignedUsersListPagination->pageSize)
        );
    }
}
