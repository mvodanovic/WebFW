<?php

namespace WebFW\Media\DBLayer;

use WebFW\Core\Config;
use WebFW\Core\Router;
use WebFW\Database\TableGateway;
use WebFW\Media\Controllers\InternalImage;
use WebFW\Media\DBLayer\Tables\Image as ImageT;

class Image extends TableGateway
{
    protected static $imageTypes = array(
        ImageT::TYPE_EXTERNAL => 'External',
        ImageT::TYPE_INTERNAL => 'Internal',
    );

    public function __construct()
    {
        $this->setTable(ImageT::getInstance());
        parent::__construct();
    }

    public function getImageType()
    {
        if (array_key_exists($this->image_type_id, static::$imageTypes)) {
            return static::$imageTypes[$this->image_type_id];
        }

        return null;
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function getURL()
    {
        switch ($this->image_type_id) {
            case ImageT::TYPE_EXTERNAL:
                return $this->uri;
            case ImageT::TYPE_INTERNAL:
                return Router::getInstance()->URL(InternalImage::className(), null, array('caption' => $this->caption));
        }

        return null;
    }

    public function getFileName()
    {
        $name = $this->getCaption();
        if ($name === null) {
            $name = $this->image_id;
        }

        switch ($this->extension) {
            case ImageT::EXT_JPG:
                $name .= '.jpg';
                break;
            case ImageT::EXT_PNG:
                $name .= '.png';
                break;
            case ImageT::EXT_GIF:
                $name .= '.gif';
                break;
        }

        return $name;
    }

    public function getContentType()
    {
        switch ($this->extension) {
            case ImageT::EXT_JPG:
                return 'image/jpeg';
            case ImageT::EXT_PNG:
                return 'image/png';
            case ImageT::EXT_GIF:
                return 'image/gif';
        }

        return 'application/octet-stream';
    }
}
