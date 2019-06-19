<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Swagger\Annotations as SWG;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class User implements UserInterface
{
    public const SENIORITY_JUNIOR = 0;
    public const SENIORITY_MIDDLE = 1;
    public const SENIORITY_SENIOR = 2;

    /**
     * User ID
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     * @Groups({"UserDetail", "ActivityDetails", "ActivityCreate", "ActivityEdit"})
     * @SWG\Property()
     */
    protected $id;

    /**
     * The username of User
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     * @SWG\Property()
     */
    private $username;

    /**
     * Password of User
     * @ORM\Column(type="string")
     * @SWG\Property()
     */
    private $password;

    /**
     * User email
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     * @SWG\Property()
     */
    protected $email;

    /**
     * User position
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     * @SWG\Property()
     */
    private $position;

    /**
     * User seniority (JUNIOR, MIDDLE, SENIOR int(0-2) )
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     * @SWG\Property()
     */
    private $seniority = self::SENIORITY_JUNIOR;

    /**
     * The name of User
     * @ORM\Column(type="string")
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     * @SWG\Property()
     */
    private $name;

    /**
     * The surname of User
     * @ORM\Column(type="string")
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     * @SWG\Property()
     */
    private $surname;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     */
    private $updatedAt;

    /**
     * User Technologies (Technology Collection)
     * @var Collection|Technology[]
     * @ORM\ManyToMany(targetEntity="Technology")
     * @Serializer\Expose()
     * @Groups({"UserDetail"})
     * @SWG\Property()
     */
    protected $technologies;

    public function __construct()
    {
        $this->technologies = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * @return Technology[]
     */
    public function getTechnologies(): ?iterable
    {
        return $this->technologies;
    }

    /**
     * @param Technology $technology
     */
    public function addTechnology(Technology $technology): void
    {
        if ($this->technologies->contains($technology)) {
            return;
        }
        $this->technologies->add($technology);
    }

    /**
     * @param Technology $technology
     */
    public function removeTechnology(Technology $technology): void
    {
        if (!$this->technologies->contains($technology)) {
            return;
        }
        $this->technologies->removeElement($technology);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    public function getSeniority(): ?string
    {
        return $this->seniority;
    }

    public function setSeniority(string $seniority): void
    {
        $this->seniority = $seniority;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getRoles(): array
    {
        return array('ROLE_USER');
    }

    public function getSalt()
    {
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new DateTime('now'));
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new DateTime('now'));
        }
    }
}
