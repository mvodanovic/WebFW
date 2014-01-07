<?php

namespace WebFW\Media\Classes;

use WebFW\Core\Classes\BaseClass;
use WebFW\Core\Config;
use WebFW\Core\Exception;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Media\DBLayer\Image;
use WebFW\Media\DBLayer\ImageAspectRatio;
use WebFW\Media\DBLayer\ImageAspectRatioCrop;
use WebFW\Media\DBLayer\ListFetchers\ImageVariation;
use WebFW\Media\DBLayer\Tables\ImageVariation as TImageVariation;
use WebFW\Media\DBLayer\ImageVariation as TGImageVariation;
use WebFW\Media\Processors\ImageProcessor;

class ImageHelper extends BaseClass
{
    const IMAGES_PER_SLOT = 10000;

    const TYPE_JPEG = 'image/jpeg';
    const TYPE_PNG = 'image/png';
    const TYPE_GIF = 'image/gif';

    protected static $contentTypes = array(
        self::TYPE_JPEG,
        self::TYPE_PNG,
        self::TYPE_GIF,
    );

    public static function getContentTypeByExtension($extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'jpe':
                return static::TYPE_JPEG;
            case 'png':
                return static::TYPE_PNG;
            case 'gif':
                return static::TYPE_GIF;
        }

        return static::TYPE_JPEG;
    }

    public static function getSupportedTypes()
    {
        return static::$contentTypes;
    }

    protected static function getDirectorySlot($imageId)
    {
        return ceil($imageId / static::IMAGES_PER_SLOT);
    }

    public static function getImagePath($imageId, $variation = null)
    {
        return static::getImageDirectory($imageId, $variation) . DIRECTORY_SEPARATOR . $imageId;
    }

    public static function getImageDirectory($imageId, $variation = null)
    {
        $path = Config::get('General', 'mediaPath');
        if ($path === null) {
            $path = 'media';
        }
        $path = \WebFW\Core\BASE_PATH . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 'images';
        if ($variation !== null) {
            $path .= DIRECTORY_SEPARATOR . 'variations' . DIRECTORY_SEPARATOR . $variation;
        }
        $path .= DIRECTORY_SEPARATOR . static::getDirectorySlot($imageId);

        return $path;
    }

    public static function createVariation($imageId, $variation)
    {
        $variationObject = new TGImageVariation();
        try {
            $variationObject->load($variation);
        } catch (NotFoundException $e) {
            throw new Exception('Variation does not exist: ' . $variation, 500, $e);
        }

        $fullImagePath = static::getImagePath($imageId);
        $variationImageDirectory = static::getImageDirectory($imageId, $variation);
        $processor = ImageProcessor::getInstance();
        $processor->setSourcePath($fullImagePath);
        switch ($variationObject->type) {
            case TImageVariation::TYPE_CROPPED:
                $aspectRatioCrop = new ImageAspectRatioCrop();
                try {
                    $aspectRatioCrop->loadBy(array('image_id' => $imageId, 'aspect_ratio_id' => $variationObject->aspect_ratio_id));
                    $processor->cropImage(
                        $aspectRatioCrop->x,
                        $aspectRatioCrop->y,
                        (int) round($aspectRatioCrop->ratio_factor / $variationObject->getAspectRatio()->width),
                        (int) round($aspectRatioCrop->ratio_factor / $variationObject->getAspectRatio()->width * $variationObject->height / $variationObject->width)
                    );
                } catch (NotFoundException $e) {
                    $processor->autocropImage($variationObject->width, $variationObject->height);
                }
                $processor->resizeImage($variationObject->width, $variationObject->height);
                break;
            case TImageVariation::TYPE_MAX_WIDTH:
            case TImageVariation::TYPE_MAX_HEIGHT:
            case TImageVariation::TYPE_CONTAIN:
                $processor->resizeImage($variationObject->width, $variationObject->height);
                break;
            case TImageVariation::TYPE_COVER:
                $processor->resizeImage($variationObject->width, $variationObject->height, true);
                break;
        }
        $processor->saveImageToDisk($variationImageDirectory, $imageId);
    }

    public static function deleteVariations($imageId)
    {
        $variationsLF = new ImageVariation();
        $variationsLF->setGetObjectListFlag(false);
        $variations = $variationsLF->getList();
        foreach ($variations as &$variationDef) {
            $file = static::getImagePath($imageId, $variationDef['variation']);
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }

    public static function saveImageToDisk($originPath, $imageId, $variation = null)
    {
        if (!file_exists($originPath)) {
            /// TODO: error reporting
            return;
        }

        $imageDirectory = static::getImageDirectory($imageId, $variation);
        if (!file_exists($imageDirectory)) {
            mkdir($imageDirectory, Config::get('General', 'directoryUmask'), true);
        }
        if (!file_exists($imageDirectory)) {
            /// TODO: error reporting
            return;
        }
        $newPath = static::getImagePath($imageId, $variation);

        if (!rename($originPath, $newPath)) {
            /// TODO: error reporting
        }
    }

    public static function getCrop(Image $image, ImageAspectRatio $ratio)
    {
        $aspectRatioCrop = new ImageAspectRatioCrop();
        try {
            $aspectRatioCrop->loadBy(array(
                'image_id' => $image->image_id,
                'aspect_ratio_id' => $ratio->aspect_ratio_id,
            ));
            $crop = array(
                'x' => $aspectRatioCrop->x,
                'y' => $aspectRatioCrop->y,
                'w' => $aspectRatioCrop->ratio_factor / $ratio->width,
                'h' => $aspectRatioCrop->ratio_factor / $ratio->height,
            );
        } catch (NotFoundException $e) {
            $crop = static::getDefaultCrop($image->width, $image->height, $ratio->width, $ratio->height);
        }

        return $crop;
    }

    public static function getDefaultCrop($imageWidth, $imageHeight, $ratioWidth, $ratioHeight)
    {
        $widthFactor = $imageWidth / $ratioWidth;
        $heightFactor = $imageHeight / $ratioHeight;
        $factor = $widthFactor < $heightFactor ? $widthFactor : $heightFactor;

        $w = $ratioWidth * $factor;
        $h = $ratioHeight * $factor;

        $x = ($imageWidth - $w) / 2;
        $y = ($imageHeight - $h) / 2;

        $crop = array(
            'x' => round($x),
            'y' => round($y),
            'w' => round($w),
            'h' => round($h),
        );

        return $crop;
    }
}
