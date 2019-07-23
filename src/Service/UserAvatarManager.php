<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserAvatarManager
{
    /**
     * @var ImageCropperResizer
     */
    private $cropperResizer;
    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @var int
     */
    private $profilePicWidth;

    /**
     * @var int
     */
    private $profilePicHeight;

    /**
     * @var int
     */
    private $thumbnailPicWidth;

    /**
     * @var int
     */
    private $thumbnailPicHeight;

    public function __construct(
        string $targetDirectory,
        array $avatarSize,
        ImageCropperResizer $cropperResizer
    ) {
        $this->cropperResizer = $cropperResizer;
        $this->targetDirectory = $targetDirectory;
        $this->profilePicWidth = $avatarSize['profile']['width'];
        $this->profilePicHeight = $avatarSize['profile']['height'];
        $this->thumbnailPicWidth = $avatarSize['thumbnail']['width'];
        $this->thumbnailPicHeight = $avatarSize['thumbnail']['height'];
    }

    public function createImage(UploadedFile $uploadedFile, User $user): Image
    {
        $image = new Image();
        $filename = $user->getId() . '.' . $uploadedFile->guessExtension();
        $image->setFile($filename);
        $image->setAlt($user->getUsername());

        return $image;
    }

    public function saveAvatarInDirectory(UploadedFile $uploadedFile, User $user): void
    {

        $filename = $user->getId() . '.' . $uploadedFile->guessExtension();

        $croppedImage = $this->cropperResizer->centerSquareCrop($uploadedFile);

        $normalUserAvatar = $this->cropperResizer
            ->resize($croppedImage, $this->profilePicWidth, $this->profilePicHeight);
        $normalUserAvatar
            ->move(
                $this->targetDirectory
                . $this->profilePicWidth . 'x'
                . $this->profilePicHeight . '/',
                $filename
            );

        $thumbnailUserAvatar = $this->cropperResizer
            ->resize($croppedImage, $this->thumbnailPicWidth, $this->thumbnailPicHeight);
        $thumbnailUserAvatar
            ->move(
                $this->targetDirectory
                . $this->thumbnailPicWidth . 'x'
                . $this->thumbnailPicHeight . '/',
                $filename
            );

        $uploadedFile->move($this->targetDirectory . 'Original/', $filename);
    }

    public function removeAvatarFromDirectory(User $user): void
    {

        $image = $user->getAvatar();

        $filename = $image->getFile();
        $filepaths = array(
            [
                'original' =>
                    $this->targetDirectory . 'Original/' . $filename,
                'profile' =>
                    $this->targetDirectory
                    . $this->profilePicWidth . 'x'
                    . $this->profilePicHeight . '/'
                    . $filename,
                'thumbnail' =>
                    $this->targetDirectory
                    . $this->thumbnailPicWidth . 'x'
                    . $this->thumbnailPicHeight . '/'
                    . $filename
            ]
        );

        $filesystem = new Filesystem();
        foreach ($filepaths as $filepath) {
            $filesystem->remove($filepath);
        }
    }
}
