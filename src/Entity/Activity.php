<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityRepository")
 */
class Activity
{
    public const STATUS_NEW = 0;
    public const STATUS_FINISHED = 1;
    public const STATUS_CLOSED = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
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
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_NEW;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $owner;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var Collection|Technology[]
     * @ORM\ManyToMany(targetEntity="Technology")
     */
    protected $technologies;

    /**
     * @var Collection|Type[]
     * @ORM\ManyToMany(targetEntity="Type")
     */
    protected $types;

    public function __construct()
    {
        $this->technologies = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @param Technology $technology
     */
    public function addActivityTechnology(Technology $technology): void
    {
        if ($this->technologies->contains($technology)) {
            return;
        }
        $this->technologies->add($technology);
    }

    /**
     * @param Technology $technology
     */
    public function removeActivityTechnology(Technology $technology): void
    {
        if (!$this->technologies->contains($technology)) {
            return;
        }
        $this->technologies->removeElement($technology);
    }

    /**
     * @param Type $type
     */
    public function addActivityType(Type $type): void
    {
        if ($this->types->contains($type)) {
            return;
        }
        $this->types->add($type);
    }

    /**
     * @param Type $type
     */
    public function removeActivityType(Type $type): void
    {
        if (!$this->types->contains($type)) {
            return;
        }
        $this->types->removeElement($type);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getApplicationDeadline(): ?\DateTimeInterface
    {
        return $this->applicationDeadline;
    }

    public function setApplicationDeadline(\DateTimeInterface $applicationDeadline): void
    {
        $this->applicationDeadline = $applicationDeadline;
    }

    public function getFinalDeadline(): ?\DateTimeInterface
    {
        return $this->finalDeadline;
    }

    public function setFinalDeadline(\DateTimeInterface $finalDeadline): void
    {
        $this->finalDeadline = $finalDeadline;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getOwner(): ?int
    {
        return $this->owner;
    }

    public function setOwner(int $owner): void
    {
        $this->owner = $owner;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
