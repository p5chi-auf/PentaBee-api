<?php

namespace App\Service;

use App\Entity\Image;

class ImageManager
{
    /**
     * @var UserAvatarManager
     */
    private $userAvatarManager;

    public function __construct(UserAvatarManager $userAvatarManager)
    {
        $this->userAvatarManager = $userAvatarManager;
    }

    public function getAvailableResolutions(Image $image): array
    {
        if ($image->getLinkedTo() === Image::IMAGE_TYPE_USER) {
            return $this->userAvatarManager->getAvatarResolutions($image);
        }

        return array();
        //TO DO getAvailableResolutions for activities if($image->getLinkedTo === 'activity')
    }
}
