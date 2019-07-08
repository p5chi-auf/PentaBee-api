<?php

namespace App\Handlers;

use App\Entity\User;
use App\Filters\ActivityListFilter;
use App\Filters\ActivityListPagination;
use App\Filters\ActivityListSort;
use App\Repository\ActivityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class ActivityHandler
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var ActivityRepository
     */
    private $activityRepository;

    public function __construct(SerializerInterface $serializer, ActivityRepository $activityRepository)
    {

        $this->serializer = $serializer;
        $this->activityRepository = $activityRepository;
    }

    public function getActivitiesListPaginated(
        ActivityListPagination $activityListPagination,
        ActivityListSort $activityListSort,
        ActivityListFilter $activityListFilter,
        User $user
    ): array {
        $paginatedResults = $this->activityRepository
            ->getPaginatedActivities($activityListPagination, $activityListSort, $activityListFilter, $user);

        $paginator = new Paginator($paginatedResults);
        $numResults = $paginator->count();

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('ActivityList'));

        $json = $this->serializer->serialize(
            $paginatedResults->getResult(),
            'json',
            $context
        );

        return array(
            'results' => json_decode($json, true),
            'currentPage' => $activityListPagination->currentPage,
            'numResults' => $numResults,
            'perPage' => $activityListPagination->pageSize,
            'numPages' => (int)ceil($numResults / $activityListPagination->pageSize)
        );
    }
}
