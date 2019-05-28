<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\User;

class UserTransformer
{
    public function transform(
        UserDTO $dto
    ): User {

        $entity = new User();
        $entity->setName($dto->name);
        $entity->setPassword($dto->password);
        $entity->setSurname($dto->surname);
        $entity->setUsername($dto->username);
        $entity->setCreatedAt($dto->createdAt);
        $entity->setUpdatedAt($dto->updatedAt);
        $entity->setEmail($dto->email);
        return $entity;
    }
}
