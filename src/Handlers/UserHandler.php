<?php

namespace App\Handlers;

use App\Entity\User;
use App\Filters\UserListFilter;
use App\Filters\UserListPagination;
use App\Filters\UserListSort;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

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

    public function __construct(SerializerInterface $serializer, UserRepository $userRepository)
    {

        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
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

        return array(
            'results' => json_decode($json, true),
            'currentPage' => $userListPagination->currentPage,
            'numResults' => $numResults,
            'perPage' => $userListPagination->pageSize,
            'numPages' => (int)ceil($numResults / $userListPagination->pageSize)
        );
    }
}
