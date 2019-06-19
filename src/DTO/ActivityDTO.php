<?php


namespace App\DTO;

use App\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class ActivityDTO
{
    public const STATUS_NEW = 0;
    public const STATUS_FINISHED = 1;
    public const STATUS_CLOSED = 2;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\Expose()
     */
    public $id;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(
     *     message = "Activity name cannot be blank!",
     *     groups={"ActivityEdit", "ActivityCreate"}
     * )
     * @Serializer\Expose()
     * @Groups({"ActivityEdit", "ActivityCreate"})
     */
    public $name;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(
     *     message = "Activity description cannot be blank!",
     *     groups={"ActivityEdit", "ActivityCreate"}
     * )
     * @Serializer\Expose()
     * @Groups({"ActivityEdit", "ActivityCreate"})
     */
    public $description;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Assert\GreaterThan("now", groups={"ActivityEdit", "ActivityCreate"})
     * @Serializer\Expose()
     * @Groups({"ActivityEdit", "ActivityCreate"})
     */
    public $applicationDeadline;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Assert\GreaterThan(propertyPath="applicationDeadline",
     *     groups={"ActivityEdit", "ActivityCreate"}
     * )
     * @Serializer\Expose()
     * @Groups({"ActivityEdit", "ActivityCreate"})
     */
    public $finalDeadline;

    /**
     * @var integer
     * @Serializer\Type("integer")
     * @Assert\Range(
     *     min = 0,
     *     max = 2,
     *     minMessage="Status must be in range 0-2!",
     *     maxMessage="Status must be in range 0-2!",
     *     groups={"ActivityEdit", "ActivityCreate"}
     * )
     * @Serializer\Expose()
     * @Groups({"ActivityEdit", "ActivityCreate"})
     *
     */
    public $status = self::STATUS_NEW;

    /**
     * @var User
     * @Serializer\Type(User::class)
     * @Assert\NotNull(
     *     message="Activity must have an Owner!",
     *     groups={"ActivityEdit", "ActivityCreate"}
     * )
     * @Serializer\Expose()
     * @Groups({"ActivityEdit", "ActivityCreate"})
     */
    public $owner;

    /**
     * @var Collection|TechnologyDTO[]
     * @Serializer\Type("ArrayCollection<App\DTO\TechnologyDTO>")
     * @Serializer\Expose()
     * @Groups({"ActivityEdit", "ActivityCreate"})
     */
    public $technologies;

    /**
     * @var Collection|ActivityTypeDTO[]
     * @Serializer\Type("ArrayCollection<App\DTO\ActivityTypeDTO>")
     * @Serializer\Expose()
     * @Groups({"ActivityEdit", "ActivityCreate"})
     */
    public $types;

    public function __construct()
    {
        $this->technologies = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }
}
