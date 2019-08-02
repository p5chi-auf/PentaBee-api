<?php

namespace App\Handlers;

use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\User;
use App\Filters\FeedbackPagination;
use App\Filters\FeedbackSort;
use App\Repository\FeedbackRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class FeedbackHandler
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var FeedbackRepository
     */
    private $feedbackRepository;

    public function __construct(SerializerInterface $serializer, FeedbackRepository $feedbackRepository)
    {
        $this->serializer = $serializer;
        $this->feedbackRepository = $feedbackRepository;
    }

    public function getUserFeedbackPaginated(
        User $user,
        FeedbackSort $feedbackSort,
        FeedbackPagination $feedbackPagination
    ): array {
        $paginatedResults = $this->feedbackRepository
            ->getUserFeedbackPaginated($user, $feedbackSort, $feedbackPagination);

        $paginator = new Paginator($paginatedResults);
        $numResults = $paginator->count();

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('FeedbackList'));

        $json = $this->serializer->serialize(
            $paginatedResults->getResult(),
            'json',
            $context
        );

        return array(
            'results' => json_decode($json, true),
            'currentPage' => $feedbackPagination->currentPage,
            'numResults' => $numResults,
            'perPage' => $feedbackPagination->pageSize,
            'numPages' => (int)ceil($numResults / $feedbackPagination->pageSize)
        );
    }
}
