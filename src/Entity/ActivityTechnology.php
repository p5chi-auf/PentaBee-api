<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityTechnologyRepository")
 */
class ActivityTechnology
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
    private $idTechnology;

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

    public function getIdTechnology(): ?int
    {
        return $this->idTechnology;
    }

    public function setIdTechnology(int $idTechnology): self
    {
        $this->idTechnology = $idTechnology;

        return $this;
    }
}
