<?php

namespace App\Handlers;

use App\Entity\Activity;
use App\Entity\ActivityUser;
use App\Repository\ActivityUserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    public function getApplicantsPaginated(Activity $activity, int $page): JsonResponse
    {
        $paginatedResults = $this->activityUserRepository->getApplicantsForActivityPaginated($activity, $page);

        $paginator = new Paginator($paginatedResults);
        $numResults = $paginator->count();

        $paginatedResults = $paginatedResults->getResult();

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('UserList'));

        $json = $this->serializer->serialize(
            $paginatedResults,
            'json',
            $context
        );

        return new JsonResponse([
            'results' => json_decode($json, true),
            'numResults' => $numResults,
            'perPage' => ActivityUser::NUM_APPLICANTS_PER_PAGE,
            'numPages' => (int)ceil($numResults / ActivityUser::NUM_APPLICANTS_PER_PAGE),
        ], Response::HTTP_OK);
    }
}
