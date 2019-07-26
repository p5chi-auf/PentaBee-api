<?php

namespace App\Serializer;

use App\Entity\Image;
use App\Service\ImageManager;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

class ImageSerializationHandler implements SubscribingHandlerInterface
{
    /**
     * @var ImageManager
     */
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * @return array
     */
    public static function getSubscribingMethods(): array
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Image::class,
                'method' => 'serializeImageToJson',
            )
        );
    }

    public function serializeImageToJson(
        JsonSerializationVisitor $visitor,
        Image $image,
        array $type,
        Context $context
    ): array {
        return $this->imageManager->getAvailableResolutions($image);
    }
}
