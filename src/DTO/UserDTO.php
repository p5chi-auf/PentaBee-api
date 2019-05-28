<?php

namespace App\DTO;

use App\Entity\Technology;
use DateTime;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    public const SENIORITY_JUNIOR = 0;
    public const SENIORITY_MIDDLE = 1;
    public const SENIORITY_SENIOR = 2;

    /**
     * @var integer
     * @Serializer\Type("integer")
     */
    public $id;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your first name must be at least 4 characters long",
     *      maxMessage = "Your first name cannot be longer than 50 characters"
     * )
     */
    public $username;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull
     * @Assert\Regex("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/", message = "password
     *     must be a minimum of 8 characters 1 number, one uppercase character, one lowercase character"
     * )
     */
    public $password;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     */
    public $email;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    public $position;

    /**
     * @var integer
     * @Serializer\Type("integer")
     */
    public $seniority = self::SENIORITY_JUNIOR;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull
     */
    public $name;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull
     */
    public $surname;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Assert\NotNull
     */
    public $createdAt;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Assert\NotNull
     */
    public $updatedAt;

    /**
     * @var Collection|Technology[]
     * @Serializer\Type("ArrayCollection<App\Entity\Technology>")
     */
    protected $technologies;
}