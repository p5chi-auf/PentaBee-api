<?php


namespace App\Service;

use App\DTO\ActivityTypeDTO;
use App\Entity\ActivityType;

class TypeTransformer
{
    public function transform(ActivityTypeDTO $dto): ActivityType
    {
        $entity = new ActivityType();
        $entity->setName($dto->name);
        $entity->setDescription($dto->description);
        $entity->setCreatedAt($dto->createdAt);
        $entity->setUpdatedAt($dto->updatedAt);

        return $entity;
    }

    public function inverseTransform(ActivityType $entity): ActivityTypeDTO
    {
        $dto = new ActivityTypeDTO();
        $dto->name = $entity->getName();
        $dto->description = $entity->getDescription();
        $dto->createdAt = $entity->getCreatedAt();
        $dto->updatedAt = $entity->getUpdatedAt();

        return $dto;
    }
}
