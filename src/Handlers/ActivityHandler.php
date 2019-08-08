<?php

namespace App\Handlers;

use App\Entity\User;
use App\Filters\ActivityListFilter;
use App\Filters\ActivityListPagination;
use App\Filters\ActivityListSort;
use App\Filters\PaginatorPageFieldValidator;
use App\Repository\ActivityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    /**
     * @var PaginatorPageFieldValidator
     */
    private $parameterValidator;

    public function __construct(
        SerializerInterface $serializer,
        ActivityRepository $activityRepository,
        PaginatorPageFieldValidator $parameterValidator
    ) {

        $this->serializer = $serializer;
        $this->activityRepository = $activityRepository;
        $this->parameterValidator = $parameterValidator;
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

        $numPages = (int)ceil($numResults / $activityListPagination->pageSize);
        if (!$this->parameterValidator->isParameterValid($numPages, $activityListPagination->currentPage)) {
            throw new NotFoundHttpException();
        }


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
            'numPages' => $numPages
        );
    }
}
