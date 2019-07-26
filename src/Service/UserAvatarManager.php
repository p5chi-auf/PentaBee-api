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
        $image->setLinkedTo(Image::IMAGE_TYPE_USER);

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

    public function getAvatarResolutions(Image $image): array
    {
        $filename = $image->getFile();
        $currentDirectory = substr($this->targetDirectory, strpos($this->targetDirectory, 'public'));
        $resolutions = [];
        $resolutions['original'] = $currentDirectory . 'Original/' . $filename;
        foreach ($this->avatarSize as $size) {
            $directoryFormat = '%sx%s';
            $directory = sprintf($directoryFormat, $size['width'], $size['height']);
            $pathFormat = '%s%s/%s';
            $path = sprintf($pathFormat, $currentDirectory, $directory, $filename);
            $resolutions[$directory] = $path;
        }

        return $resolutions;
    }
}
