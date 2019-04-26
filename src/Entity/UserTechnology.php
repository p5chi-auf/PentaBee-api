<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserTechnologyRepository")
 */
class UserTechnology
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
    private $idUser;

    /**
     * @ORM\Column(type="integer")
     */
    private $idTechnology;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function setIdUser(int $idUser): self
    {
        $this->idUser = $idUser;

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
