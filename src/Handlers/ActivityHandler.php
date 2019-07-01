<?php

namespace App\Handlers;

use App\Entity\Activity;
use App\Entity\User;
use App\Repository\ActivityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    public function getActivitiesListPaginated(User $user, int $page): JsonResponse
    {
        $paginatedResults = $this->activityRepository->getPaginatedActivities($user, $page);

        $paginator = new Paginator($paginatedResults);
        $numResults = $paginator->count();

        $paginatedResults = $paginatedResults->getResult();

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('ActivityList'));

        $json = $this->serializer->serialize(
            $paginatedResults,
            'json',
            $context
        );

        return new JsonResponse([
            'results' => json_decode($json, true),
            'numResults' => $numResults,
            'perPage' => Activity::NUM_ITEMS_PER_PAGE,
            'numPages' => (int)ceil($numResults / Activity::NUM_ITEMS_PER_PAGE),
        ], Response::HTTP_OK);
    }
}
