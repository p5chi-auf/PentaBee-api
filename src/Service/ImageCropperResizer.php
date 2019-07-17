<?php

namespace App\Service;

use App\Base64EncodedFileTransformers\Base64EncodedFile;
use App\Base64EncodedFileTransformers\UploadedBase64EncodedFile;
use Imagick;
use ImagickException;

class ImageCropperResizer
{
    public function avatarCropResize(UploadedBase64EncodedFile $userAvatar): UploadedBase64EncodedFile
    {
        $filepath = $userAvatar->getPathname();

        try {
            $image = new Imagick(realpath($filepath));
        } catch (ImagickException $e) {
        }
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        if ($width > $height) {
            $image->cropImage($height, $height, ($width - $height) / 2, 0);
        } elseif ($height > $width) {
            $image->cropImage($width, $width, 0, ($height - $width) / 2);
        }

        $image->resizeImage(256, 256, 0, 1);

        return new UploadedBase64EncodedFile(new Base64EncodedFile(base64_encode($image)));
    }
}
