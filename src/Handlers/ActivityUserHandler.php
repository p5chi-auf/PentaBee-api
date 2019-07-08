<?php

namespace App\Handlers;

use App\Entity\Activity;
use App\Filters\ApplicantsListPagination;
use App\Filters\ApplicantsListSort;
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

    public function getApplicantsPaginated(
        ApplicantsListSort $applicantsListSort,
        ApplicantsListPagination $applicantsListPagination,
        Activity $activity
    ): array {
        $paginatedResults = $this->activityUserRepository
            ->getApplicantsForActivityPaginated($applicantsListSort, $applicantsListPagination, $activity);

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
            'currentPage' => $applicantsListPagination->currentPage,
            'numResults' => $numResults,
            'perPage' => $applicantsListPagination->pageSize,
            'numPages' => (int)ceil($numResults / $applicantsListPagination->pageSize)
        );
    }
}
