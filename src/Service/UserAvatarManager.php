<?php

namespace App\Service;

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
    private $avatarSize;
    /**
     * @var int
     */
    private $thumbnailSize;

    public function __construct(
        string $targetDirectory,
        int $avatarSize,
        int $thumbnailSize,
        ImageCropperResizer $cropperResizer
    ) {
        $this->cropperResizer = $cropperResizer;
        $this->targetDirectory = $targetDirectory;
        $this->avatarSize = $avatarSize;
        $this->thumbnailSize = $thumbnailSize;
    }

    public function saveAvatarInDirectory(UploadedFile $uploadedFile, User $user): void
    {
        $filename = $user->getId() . '.' . $uploadedFile->guessExtension();

        $croppedImage = $this->cropperResizer->centerSquareCrop($uploadedFile);
        $resizedImage256 = $this->cropperResizer->resize($croppedImage, $this->avatarSize, $this->avatarSize);
        $resizedImage256
            ->move($this->targetDirectory . $this->avatarSize . 'x' . $this->avatarSize . '/', $filename);

        $resizedImage40 = $this->cropperResizer->resize($croppedImage, $this->thumbnailSize, $this->thumbnailSize);
        $resizedImage40
            ->move($this->targetDirectory . $this->thumbnailSize . 'x' . $this->thumbnailSize . '/', $filename);

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
                'avatar' =>
                    $this->targetDirectory . $this->avatarSize . 'x' . $this->avatarSize . '/' . $filename,
                'thumbnail' =>
                    $this->targetDirectory . $this->thumbnailSize . 'x' . $this->thumbnailSize . '/' . $filename
            ]
        );

        $filesystem = new Filesystem();
        foreach ($filepaths as $filepath) {
            $filesystem->remove($filepath);
        }
    }
}
