<?php

namespace App\Service;

use App\DTO\ActivityDTO;
use App\Entity\Activity;
use App\Entity\Technology;
use App\Entity\Type;
use App\Entity\User;
use App\Exceptions\EntityNotFound;
use App\Repository\TechnologyRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class ActivityTransformer
{

    /**
     * @var UserRepository
     */
    private $userRepo;
    /**
     * @var TechnologyRepository
     */
    private $techRepo;
    /**
     * @var TypeRepository
     */
    private $typeRepo;

    public function __construct(
        UserRepository $userRepo,
        TechnologyRepository $techRepo,
        TypeRepository $typeRepo
    ) {
        $this->userRepo = $userRepo;
        $this->techRepo = $techRepo;
        $this->typeRepo = $typeRepo;
    }

    /**
     * @param ActivityDTO $dto
     * @return Activity
     * @throws EntityNotFound
     */
    public function transform(
        ActivityDTO $dto
    ): Activity {

        $entity = new Activity();
        $entity->setName($dto->name);
        $entity->setDescription($dto->description);
        $entity->setApplicationDeadline($dto->applicationDeadline);
        $entity->setFinalDeadline($dto->finalDeadline);
        $entity->setCreatedAt($dto->createdAt);
        $entity->setUpdatedAt($dto->updatedAt);
        $entity->setStatus($dto->status);

        /** @var User $tempUser */
        $tempUser = $this->userRepo->find($dto->owner);
        $entity->setOwner($tempUser);

        /** @var Technology $tech */
        foreach ($dto->technologies as $tech) {
            $techID = $tech->id;
            $techToAdd = $this->techRepo->find($techID);
            if (!$techToAdd) {
                $entityNotFound = new EntityNotFound(
                    Technology::class,
                    $techID,
                    'No technology found.'
                );
                throw $entityNotFound;
            }
            $entity->addTechnology($techToAdd);
        }

        /** @var Type $activityType */
        foreach ($dto->types as $activityType) {
            $activityTypeID = $activityType->id;
            $activityTypeToAdd = $this->typeRepo->find($activityTypeID);
            if (!$activityTypeToAdd) {
                $entityNotFound = new EntityNotFound(
                    Type::class,
                    $activityTypeID,
                    'No activity type found.'
                );
                throw $entityNotFound;
            }
            $entity->addType($activityTypeToAdd);
        }
        return $entity;
    }
}
