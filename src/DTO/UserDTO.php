<?php

namespace App\DTO;

use DateTime;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class UserDTO
{
    public const SENIORITY_JUNIOR = 0;
    public const SENIORITY_MIDDLE = 1;
    public const SENIORITY_SENIOR = 2;

    /**
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit", "PasswordEdit"})
     */
    public $id;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"UserCreate", "UserEdit"})
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your first name must be at least 4 characters long",
     *      maxMessage = "Your first name cannot be longer than 50 characters",
     *     groups={"UserCreate", "UserEdit"}
     * )
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     */
    public $username;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Groups({"UserCreate", "PasswordEdit"})
     * @Assert\NotNull(groups={"UserCreate", "PasswordEdit"})
     * @Assert\Regex("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[-_!@#$%^&*])\S*$/",
     * message = "password requirements(at least):length >8, 1 uppercase, 1 lowercase, 1 digit, 1 special",
     * groups={"UserCreate", "PasswordEdit"}
     * )
     */
    public $password;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"PasswordEdit"})
     * @Assert\Regex("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[-_!@#$%^&*])\S*$/",
     * message = "password requirements(at least):length >8, 1 uppercase, 1 lowercase, 1 digit, 1 special",
     * groups={"PasswordEdit"}
     * )
     * @Serializer\Expose()
     * @Groups({"PasswordEdit"})
     */
    public $newPassword;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"UserCreate"})
     * @Assert\EqualTo(propertyPath="password",
     *     message="Passwords do not match.",
     *     groups={"UserCreate"}
     *     )
     * @Serializer\Expose()
     * @Groups({"UserCreate"})
     */
    public $confirmPassword;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"PasswordEdit"})
     * @Assert\EqualTo(propertyPath="newPassword",
     *     message="Passwords do not match.",
     *     groups={"PasswordEdit"}
     *     )
     * @Serializer\Expose()
     * @Groups({"PasswordEdit"})
     */
    public $confirmNewPassword;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"UserCreate", "UserEdit"})
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true,
     *     groups={"UserCreate", "UserEdit"}
     * )
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     */
    public $email;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     */
    public $position;

    /**
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     */
    public $seniority = self::SENIORITY_JUNIOR;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"UserCreate", "UserEdit"})
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     */
    public $name;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"UserCreate", "UserEdit"})
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     */
    public $surname;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Assert\NotNull(groups={"UserCreate", "UserEdit"})
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     */
    public $createdAt;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Assert\NotNull(groups={"UserCreate", "UserEdit"})
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     */
    public $updatedAt;

    /**
     * @var Collection|TechnologyDTO[]
     * @Serializer\Type("ArrayCollection<App\DTO\TechnologyDTO>")
     * @Serializer\Expose()
     * @Groups({"UserEdit"})
     */
    public $technologies;
}
