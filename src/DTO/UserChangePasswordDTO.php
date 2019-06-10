<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserChangePasswordDTO
{
    /**
     * @var integer
     * @Serializer\Type("integer")
     */
    public $id;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull
     */
    public $oldPassword;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull
     * @Assert\Regex("/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[-_!@#$%^&*])\S*$/",
     * message = "password requirements(at least):length >8, 1 uppercase, 1 lowercase, 1 digit, 1 special"
     * )
     */
    public $newPassword;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull
     * @Assert\EqualTo(propertyPath="newPassword", message="Passwords do not match.")
     */
    public $confirmNewPassword;
}
