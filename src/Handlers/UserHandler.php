<?php

namespace App\Handlers;

use App\Filters\PaginatorPageFieldValidator;
use App\Filters\UserListFilter;
use App\Filters\UserListPagination;
use App\Filters\UserListSort;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserHandler
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var PaginatorPageFieldValidator
     */
    private $parameterValidator;

    public function __construct(
        SerializerInterface $serializer,
        UserRepository $userRepository,
        PaginatorPageFieldValidator $parameterValidator
    ) {

        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
        $this->parameterValidator = $parameterValidator;
    }

    public function getUserListPaginated(
        UserListPagination $userListPagination,
        UserListSort $userListSort,
        UserListFilter $userListFilter
    ): array {

        $paginatedResults = $this->userRepository
            ->getPaginatedUserList($userListPagination, $userListSort, $userListFilter);

        $paginator = new Paginator($paginatedResults);
        $numResults = $paginator->count();

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('UserList'));

        $json = $this->serializer->serialize(
            $paginatedResults->getResult(),
            'json',
            $context
        );

        if ($userListPagination->pageSize === -1) {
            $userListPagination->pageSize = $numResults;
        }

        $numPages = (int)ceil($numResults / $userListPagination->pageSize);
        if (!$this->parameterValidator->isParameterValid($numPages, $userListPagination->currentPage)) {
            throw new NotFoundHttpException();
        }

        return array(
            'results' => json_decode($json, true),
            'currentPage' => $userListPagination->currentPage,
            'numResults' => $numResults,
            'perPage' => $userListPagination->pageSize,
            'numPages' => $numPages
        );
    }
}
