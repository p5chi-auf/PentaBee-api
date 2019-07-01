<?php

namespace App\Handlers;

use App\Entity\Activity;
use App\Repository\ActivityUserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class ActivityUserHandler
{
    public const NUM_APPLICANTS_PER_PAGE = 5;

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

    public function getApplicantsPaginated(Activity $activity, int $page): array
    {
        $paginatedResults = $this->activityUserRepository->getApplicantsForActivityPaginated($activity, $page);

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
            'numResults' => $numResults,
            'perPage' => $this::NUM_APPLICANTS_PER_PAGE,
            'numPages' => (int)ceil($numResults / $this::NUM_APPLICANTS_PER_PAGE)
        );
    }
}
