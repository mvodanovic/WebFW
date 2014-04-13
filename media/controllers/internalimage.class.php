<?php

namespace WebFW\Media\Controllers;

use WebFW\Core\Controller;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Core\Request;
use WebFW\Media\DBLayer\Image;
use WebFW\Media\DBLayer\Tables\Image as ImageT;

class InternalImage extends Controller
{
    public function execute()
    {
        $image = new Image();
        $image->load(Request::getInstance()->image_id);

        if ($image->image_type_id !== ImageT::TYPE_INTERNAL) {
            throw new NotFoundException('Not an internal image');
        }

        if (!file_exists($image->uri)) {
            throw new NotFoundException('Image not found on disk');
        }

        header('Content-type: ' . $image->getContentType());
        header('Content-disposition: attachment; filename=' . htmlspecialchars($image->getFileName()));
        $this->output = file_get_contents($image->uri);
    }
}
