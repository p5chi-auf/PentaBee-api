<?php

namespace App\DTO;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class UserDTO
{
    public const SENIORITY_JUNIOR = 0;
    public const SENIORITY_MIDDLE = 1;
    public const SENIORITY_SENIOR = 2;

    /**
     * User ID
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @SWG\Property()
     */
    public $id;

    /**
     * The username of User
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"UserCreate", "UserEdit"})
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your first name must be at least 4 characters long",
     *      maxMessage = "Your first name cannot be longer than 50 characters",
     *     groups={"UserCreate", "UserEdit"}
     * )
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     * @SWG\Property()
     */
    public $username;

    /**
     * Old Password
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"PasswordEdit"})
     * @Assert\Regex("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[-_!@#$%^&*])\S*$/",
     * message = "Password requirements(at least):length >8, 1 uppercase, 1 lowercase, 1 digit, 1 special",
     * groups={"PasswordEdit"}
     * )
     * @Serializer\Expose()
     * @Groups({"PasswordEdit"})
     * @SWG\Property()
     */
    public $oldPassword;

    /**
     * Password
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Groups({"UserCreate", "PasswordEdit"})
     * @Assert\NotNull(groups={"UserCreate", "PasswordEdit"})
     * @Assert\Regex("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[-_!@#$%^&*])\S*$/",
     * message = "Password requirements(at least):length >8, 1 uppercase, 1 lowercase, 1 digit, 1 special",
     * groups={"UserCreate", "PasswordEdit"}
     * )
     * @SWG\Property()
     */
    public $password;



    /**
     * Confirm password (===password on register or password edit)
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"UserCreate", "PasswordEdit"})
     * @Assert\EqualTo(propertyPath="password",
     *     message="Passwords do not match.",
     *     groups={"UserCreate", "PasswordEdit"}
     *     )
     * @Serializer\Expose()
     * @Groups({"UserCreate", "PasswordEdit"})
     * @SWG\Property()
     */
    public $confirmPassword;

    /**
     * User email
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"UserCreate", "UserEdit"})
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     groups={"UserCreate", "UserEdit"}
     * )
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     * @SWG\Property()
     */
    public $email;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Groups({"UserEdit"})
     */
    public $position;

    /**
     * User seniority (JUNIOR, MIDDLE, SENIOR int(0-2) )
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Groups({"UserEdit"})
     * @SWG\Property()
     */
    public $seniority = self::SENIORITY_JUNIOR;

    /**
     * The name of User
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"UserCreate", "UserEdit"})
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     * @SWG\Property()
     */
    public $name;

    /**
     * The surname of User
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"UserCreate", "UserEdit"})
     * @Serializer\Expose()
     * @Groups({"UserCreate", "UserEdit"})
     * @SWG\Property()
     */
    public $surname;

    /**
     * User Technologies (Technology Collection)
     * @var Collection|TechnologyDTO[]
     * @Serializer\Type("ArrayCollection<App\DTO\TechnologyDTO>")
     * @Serializer\Expose()
     * @Groups({"UserEdit"})
     * @SWG\Property()
     */
    public $technologies;
}
