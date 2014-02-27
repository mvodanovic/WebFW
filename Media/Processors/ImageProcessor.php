<?php

namespace mvodanovic\WebFW\Media\Processors;

use mvodanovic\WebFW\Core\Classes\BaseClass;
use mvodanovic\WebFW\Core\Config;
use mvodanovic\WebFW\Core\Exception;
use mvodanovic\WebFW\Media\Classes\ImageHelper;

abstract class ImageProcessor extends BaseClass
{
    protected $sourcePath;
    protected $imageType;
    protected $imageWidth;
    protected $imageHeight;

    protected $supportedImageTypes = array(
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG,
        IMAGETYPE_GIF,
    );

    public function setSourcePath($sourcePath)
    {
        if (!file_exists($sourcePath)) {
            throw new Exception('Source image does not exist: ' . $sourcePath);
        }

        $imageArray = getimagesize($sourcePath);
        if ($imageArray === false) {
            throw new Exception('Failed to process image: ' . $sourcePath);
        }

        if (!in_array($imageArray[2], $this->supportedImageTypes)) {
            throw new Exception('Image type not supported: ' . $imageArray['mime']);
        }

        $this->sourcePath = $sourcePath;
        $this->imageWidth = $imageArray[0];
        $this->imageHeight = $imageArray[1];
        $this->imageType = $imageArray[2];

        $this->init();
    }

    /**
     * @return ImageProcessor
     * @throws \mvodanovic\WebFW\Core\Exception
     */
    public static function getInstance()
    {
        if (extension_loaded('imagick')) {
            return new ImageProcessorImagick();
        } elseif (extension_loaded('gd')) {
            return new ImageProcessorGD();
        }

        throw new Exception('Image processing modules not found');
    }

    protected function prepareSaveDirectory($destinationDirectory)
    {
        if (!file_exists($destinationDirectory)) {
            $oldUmask = umask(0);
            $dirUmask = Config::get('General', 'directoryUmask');
            if ($dirUmask === null) {
                mkdir($destinationDirectory, 0777, true);
            } else {
                mkdir($destinationDirectory, $dirUmask, true);
            }
            umask($oldUmask);
        }

        if (!file_exists($destinationDirectory) || !is_dir($destinationDirectory)) {
            throw new Exception('Cannot create a new directory: ' . $destinationDirectory);
        }
    }

    protected function adaptDimensions(&$width, &$height, $imageAspectRatio, $cover)
    {
        $newAspectRatio = $imageAspectRatio;
        if ($width === null) {
            $width = $height * $imageAspectRatio;
        } elseif ($height === null) {
            $height = $width / $imageAspectRatio;
        } else {
            $newAspectRatio = $width / $height;
        }

        if ($newAspectRatio > $imageAspectRatio && $cover !== true
                || $newAspectRatio < $imageAspectRatio && $cover === true) {
            $width = (int) ($height * $imageAspectRatio);
        } else {
            $height = (int) ($width / $imageAspectRatio);
        }
    }

    public function autocropImage($width, $height)
    {
        $crop = ImageHelper::getDefaultCrop($this->imageWidth, $this->imageHeight, $width, $height);
        $this->cropImage($crop['x'], $crop['y'], $crop['w'], $crop['h']);
    }

    protected function init() {}
    abstract public function cropImage($x, $y, $width, $height);
    abstract public function resizeImage($width = null, $height = null, $cover = false);
    abstract public function saveImageToDisk($destinationDirectory, $filename);
}
