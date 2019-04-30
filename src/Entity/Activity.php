<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityRepository")
 */
class Activity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $applicationDeadline;

    /**
     * @ORM\Column(type="datetime")
     */
    private $finalDeadline;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $idOwner;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var \Doctrine\Common\Collections\Collection|Technology[]
     * @ORM\ManyToMany(targetEntity="Technology")
     */
    protected $activityTechnologies;

    /**
     * @var \Doctrine\Common\Collections\Collection|Type[]
     * @ORM\ManyToMany(targetEntity="Type")
     */
    protected $activityTypes;

    public function __construct()
    {
        $this->activityTechnologies = new ArrayCollection();
        $this->activityTypes = new ArrayCollection();
    }

    /**
     * @param Technology $activityTechnology
     */
    public function addActivityTechnology(Technology $activityTechnology)
    {
        if ($this->activityTechnologies->contains($activityTechnology)) {
            return;
        }
        $this->activityTechnologies->add($activityTechnology);
    }

    /**
     * @param Technology $activityTechnology
     */
    public function removeActivityTechnology(Technology $activityTechnology)
    {
        if (!$this->activityTechnologies->contains($activityTechnology)) {
            return;
        }
        $this->activityTechnologies->removeElement($activityTechnology);
    }

    /**
     * @param Type $activityType
     */
    public function addActivityType(Type $activityType)
    {
        if ($this->activityTypes->contains($activityType)) {
            return;
        }
        $this->activityTypes->add($activityType);
    }

    /**
     * @param Type $activityType
     */
    public function removeActivityType(Type $activityType)
    {
        if (!$this->activityTypes->contains($activityType)) {
            return;
        }
        $this->activityTypes->removeElement($activityType);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getApplicationDeadline(): ?\DateTimeInterface
    {
        return $this->applicationDeadline;
    }

    public function setApplicationDeadline(\DateTimeInterface $applicationDeadline): self
    {
        $this->applicationDeadline = $applicationDeadline;

        return $this;
    }

    public function getFinalDeadline(): ?\DateTimeInterface
    {
        return $this->finalDeadline;
    }

    public function setFinalDeadline(\DateTimeInterface $finalDeadline): self
    {
        $this->finalDeadline = $finalDeadline;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getIdOwner(): ?int
    {
        return $this->idOwner;
    }

    public function setIdOwner(int $idOwner): self
    {
        $this->idOwner = $idOwner;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
