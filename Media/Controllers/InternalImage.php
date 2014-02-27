<?php

namespace mvodanovic\WebFW\Media\Controllers;

use mvodanovic\WebFW\Core\Controller;
use mvodanovic\WebFW\Core\Request;
use mvodanovic\WebFW\Media\Classes\ImageHelper;
use mvodanovic\WebFW\Media\Exceptions\MediaNotFoundException;

class InternalImage extends Controller
{
    public function execute()
    {
        $imageId = (int) Request::getInstance()->image_id;
        $fileName = Request::getInstance()->filename;
        $variation = Request::getInstance()->variation;

        if ($fileName === null) {
            $fileName = $imageId . '.jpg';
        }

        $fileNameArray = explode('.', $fileName);
        $extension = array_pop($fileNameArray);

        $path = ImageHelper::getImagePath($imageId, $variation);

        if ($variation !== null && !file_exists($path)) {
            ImageHelper::createVariation($imageId, $variation);
        }

        if (!file_exists($path)) {
            throw new MediaNotFoundException('Image not found on disk: ' . $path);
        }

        header('Content-type: ' . ImageHelper::getContentTypeByExtension($extension));
        header('Content-disposition: inline; filename=' . htmlspecialchars($fileName));
        $this->output = file_get_contents($path);
    }
}
