<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityTypeRepository")
 */
class ActivityType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $idActivity;

    /**
     * @ORM\Column(type="integer")
     */
    private $idType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdActivity(): ?int
    {
        return $this->idActivity;
    }

    public function setIdActivity(int $idActivity): self
    {
        $this->idActivity = $idActivity;

        return $this;
    }

    public function getIdType(): ?int
    {
        return $this->idType;
    }

    public function setIdType(int $idType): self
    {
        $this->idType = $idType;

        return $this;
    }
}
