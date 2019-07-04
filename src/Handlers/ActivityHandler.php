<?php

namespace App\Handlers;

use App\Entity\User;
use App\Filters\ActivityListFilter;
use App\Filters\ActivityListSort;
use App\Repository\ActivityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class ActivityHandler
{
    public const NUM_ITEMS_PER_PAGE = 10;

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
        ActivityListSort $activityListSort,
        ActivityListFilter $activityListFilter,
        User $user,
        int $page
    ): array {
        $paginatedResults = $this->activityRepository
            ->getPaginatedActivities($activityListSort, $activityListFilter, $user, $page);

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
            'numResults' => $numResults,
            'perPage' => $this::NUM_ITEMS_PER_PAGE,
            'numPages' => (int)ceil($numResults / $this::NUM_ITEMS_PER_PAGE)
        );
    }
}
