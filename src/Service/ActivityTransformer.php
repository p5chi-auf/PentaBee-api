<?php

namespace App\Service;

use App\DTO\ActivityDTO;
use App\Entity\Activity;
use App\Entity\Technology;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\TechnologyRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use Exception;

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
            $techID = $tech->getId();
            $techToAdd = $this->techRepo->find($techID);
            if (!$techToAdd) {
                throw new Exception('No technology found for ID ' . $techID . '!');
            }
            $entity->addTechnology($techToAdd);
        }

        /** @var Type $activityType */
        foreach ($dto->types as $activityType) {
            $activityTypeID = $activityType->getId();
            $activityTypeToAdd = $this->typeRepo->find($activityTypeID);
            if (!$activityTypeToAdd) {
                throw new Exception('No activity type found for ID ' . $activityTypeID . '!');
            }
            $entity->addType($activityTypeToAdd);
        }
        return $entity;
    }
}
