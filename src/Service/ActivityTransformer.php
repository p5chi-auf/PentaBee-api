<?php

namespace App\Service;

use App\DTO\ActivityDTO;
use App\Entity\Activity;
use App\Entity\Technology;
use App\Entity\ActivityType;
use App\Entity\User;
use App\Exceptions\EntityNotFound;
use App\Repository\TechnologyRepository;
use App\Repository\ActivityTypeRepository;

class ActivityTransformer
{

    /**
     * @var TechnologyRepository
     */
    private $techRepo;
    /**
     * @var ActivityTypeRepository
     */
    private $typeRepo;

    public function __construct(
        TechnologyRepository $techRepo,
        ActivityTypeRepository $typeRepo
    ) {
        $this->techRepo = $techRepo;
        $this->typeRepo = $typeRepo;
    }


    /**
     * @param ActivityDTO $dto
     * @param Activity $entity
     * @throws EntityNotFound
     */
    private function addTechnologies(ActivityDTO $dto, Activity $entity): void
    {
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
    }

    /**
     * @param ActivityDTO $dto
     * @param Activity $entity
     * @throws EntityNotFound
     */
    private function addTypes(ActivityDTO $dto, Activity $entity): void
    {
        /** @var ActivityType $activityType */
        foreach ($dto->types as $activityType) {
            $activityTypeID = $activityType->id;
            $activityTypeToAdd = $this->typeRepo->find($activityTypeID);
            if (!$activityTypeToAdd) {
                $entityNotFound = new EntityNotFound(
                    ActivityType::class,
                    $activityTypeID,
                    'No activity type found.'
                );
                throw $entityNotFound;
            }
            $entity->addType($activityTypeToAdd);
        }
    }

    /**
     * @param Activity $entity
     */
    private function resetTechTypeCollections(Activity $entity): void
    {
        foreach ($entity->getTechnologies() as $techToRemove) {
            $entity->removeTechnology($techToRemove);
        }
        foreach ($entity->getTypes() as $typeToRemove) {
            $entity->removeType($typeToRemove);
        }
    }

    /**
     * @param ActivityDTO $dto
     * @param User $owner
     * @return Activity
     * @throws EntityNotFound
     */
    public function createTransform(
        ActivityDTO $dto,
        User $owner
    ): Activity {

        $entity = new Activity();
        $entity->setName($dto->name);
        $entity->setDescription($dto->description);
        $entity->setApplicationDeadline($dto->applicationDeadline);
        $entity->setFinalDeadline($dto->finalDeadline);
        $entity->setStatus($dto->status);
        $entity->setPublic($dto->public);
        $entity->setOwner($owner);

        $this->addTechnologies($dto, $entity);
        $this->addTypes($dto, $entity);

        return $entity;
    }

    /**
     * @param ActivityDTO $dto
     * @param Activity $activity
     * @return Activity
     * @throws EntityNotFound
     */
    public function editTransform(
        ActivityDTO $dto,
        Activity $activity
    ): Activity {

        $this->resetTechTypeCollections($activity);

        $activity->setName($dto->name);
        $activity->setDescription($dto->description);
        $activity->setApplicationDeadline($dto->applicationDeadline);
        $activity->setFinalDeadline($dto->finalDeadline);
        $activity->setStatus($dto->status);
        $activity->setPublic($dto->public);

        $this->addTechnologies($dto, $activity);
        $this->addTypes($dto, $activity);

        return $activity;
    }
}
