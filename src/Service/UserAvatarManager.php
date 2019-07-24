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
     * @var array
     */
    private $avatarSize;

    public function __construct(
        string $targetDirectory,
        array $avatarSize,
        ImageCropperResizer $cropperResizer
    ) {
        $this->cropperResizer = $cropperResizer;
        $this->targetDirectory = $targetDirectory;
        $this->avatarSize = $avatarSize;
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

        $uploadedFile->move($this->targetDirectory . 'Original/', $filename);

        $pathFormat = '%s%sx%s/';
        foreach ($this->avatarSize as $size) {
            $path = sprintf($pathFormat, $this->targetDirectory, $size['width'], $size['height']);
            $image = $this->cropperResizer->resize($croppedImage, $size['width'], $size['height']);
            $image->move($path, $filename);
        }
    }

    public function removeAvatarFromDirectory(User $user): void
    {

        $image = $user->getAvatar();

        $filename = $image->getFile();

        $filesystem = new Filesystem();
        $pathFormat = '%s%sx%s/%s';
        foreach ($this->avatarSize as $size) {
            $path = sprintf($pathFormat, $this->targetDirectory, $size['width'], $size['height'], $filename);
            $filesystem->remove($path);
        }
        $filesystem->remove($this->targetDirectory . 'Original/' . $filename);
    }
}
