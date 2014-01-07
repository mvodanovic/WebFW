<?php

namespace WebFW\Media\Processors;

use WebFW\Core\Config;

class ImageProcessorImagick extends ImageProcessor
{
    /** @var \Imagick */
    protected $instance;

    protected function init()
    {
        $this->instance = new \Imagick($this->sourcePath);
    }

    public function cropImage($x, $y, $width, $height)
    {
//        var_dump($x, $y, $width, $height);die;
        $this->instance->cropimage($width, $height, $x, $y);
    }

    public function resizeImage($width = null, $height = null, $cover = false)
    {
        if ($width === null && $height === null) {
            return;
        }

        $this->adaptDimensions(
            $width,
            $height,
            $this->instance->getimagewidth() / $this->instance->getimageheight(),
            $cover
        );

        $this->instance->resizeimage($width, $height, \Imagick::FILTER_LANCZOS, 1);
    }

    public function saveImageToDisk($destinationDirectory, $filename)
    {
        $this->prepareSaveDirectory($destinationDirectory);
        $filename = $destinationDirectory . DIRECTORY_SEPARATOR . $filename;

        $this->instance->writeimage($filename);

        $fileUmask = Config::get('General', 'fileUmask');
        if ($fileUmask !== null){
            chmod($filename, $fileUmask);
        }
    }
}
