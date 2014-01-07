<?php

namespace WebFW\Framework\Media\Processors;

use WebFW\Framework\Core\Config;

class ImageProcessorGD extends ImageProcessor
{
    protected $imageResource;

    protected function init()
    {
        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                $this->imagecreatefromjpegexif();
                break;
            case IMAGETYPE_PNG:
                $this->imageResource = imagecreatefrompng($this->sourcePath);
                break;
            case IMAGETYPE_GIF:
                $this->imageResource = imagecreatefromgif($this->sourcePath);
                break;
        }
    }

    public function cropImage($x, $y, $width, $height)
    {
        $newImageResource = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $newImageResource,
            $this->imageResource,
            0, 0,
            $x, $y,
            $width, $height,
            $width, $height
        );

        $this->imageResource = $newImageResource;
        $this->imageWidth = $width;
        $this->imageHeight = $height;
    }

    public function resizeImage($width = null, $height = null, $cover = false)
    {
        if ($height === null && $width === null) {
            return;
        }

        $this->adaptDimensions(
            $width,
            $height,
            $this->imageWidth / $this->imageHeight,
            $cover
        );

        $newImageResource = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $newImageResource,
            $this->imageResource,
            0, 0,
            0, 0,
            $width, $height,
            $this->imageWidth, $this->imageHeight
        );

        $this->imageResource = $newImageResource;
        $this->imageWidth = $width;
        $this->imageHeight = $height;
    }

    public function saveImageToDisk($destinationDirectory, $filename)
    {
        $this->prepareSaveDirectory($destinationDirectory);
        $filename = $destinationDirectory . DIRECTORY_SEPARATOR . $filename;

        switch ($this->imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->imageResource, $filename, 80);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->imageResource, $filename, 2);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->imageResource, $filename);
                break;
        }

        $fileUmask = Config::get('General', 'fileUmask');
        if ($fileUmask !== null){
            chmod($filename, $fileUmask);
        }
    }

    protected function imagecreatefromjpegexif()
    {
        $this->imageResource = imagecreatefromjpeg($this->sourcePath);

        if (!extension_loaded('exif')) {
            return;
        }

        $exif = exif_read_data($this->sourcePath);

        if ($exif !== false && array_key_exists('Orientation', $exif)) {
            $ort = $exif['Orientation'];

            if ($ort == 6 || $ort == 5)
                $this->imageResource = imagerotate($this->imageResource, 270, null);
            if ($ort == 3 || $ort == 4)
                $this->imageResource = imagerotate($this->imageResource, 180, null);
            if ($ort == 8 || $ort == 7)
                $this->imageResource = imagerotate($this->imageResource, 90, null);

            if ($ort == 5 || $ort == 4 || $ort == 7)
                imageflip($this->imageResource, IMG_FLIP_HORIZONTAL);
        }
    }
}
