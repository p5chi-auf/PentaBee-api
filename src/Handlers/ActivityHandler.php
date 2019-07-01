<?php

namespace App\Handlers;

use App\Entity\User;
use App\Repository\ActivityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class ActivityHandler
{
    public const NUM_ITEMS_PER_PAGE = 4;

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

    public function getActivitiesListPaginated(User $user, int $page): array
    {
        $paginatedResults = $this->activityRepository->getPaginatedActivities($user, $page);

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
