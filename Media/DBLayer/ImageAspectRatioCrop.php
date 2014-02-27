<?php

namespace mvodanovic\WebFW\Media\DBLayer;

use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use mvodanovic\WebFW\Database\TableGateway;
use mvodanovic\WebFW\Media\Classes\ImageHelper;
use mvodanovic\WebFW\Media\DBLayer\Tables\ImageAspectRatioCrop as ImageAspectRatioCropT;

/**
 * Class ImageAspectRatioCrop
 * @package mvodanovic\WebFW
 *
 * @property $image_id
 * @property $aspect_ratio_id
 * @property $ratio_factor
 * @property $x
 * @property $y
 */
class ImageAspectRatioCrop extends TableGateway
{
    protected $aspectRatioObject = null;
    protected $imageObject = null;

    public function __construct()
    {
        $this->setTable(ImageAspectRatioCropT::getInstance());
        parent::__construct();
    }

    public function getCaption()
    {
        $imageCaption = $this->image_id;
        $image = $this->getImage();
        if ($image !== null) {
            $imageCaption = $image->getCaption();
        }

        $aspectRatioCaption = $this->aspect_ratio_id;
        $aspectRatio = $this->getAspectRatio();
        if ($aspectRatio !== null) {
            $aspectRatioCaption = $aspectRatio->getCaption();
        }

        return $imageCaption . ' [' . $aspectRatioCaption . ']';
    }

    /**
     * @return ImageAspectRatio|null
     */
    public function getAspectRatio()
    {
        if ($this->aspectRatioObject === null) {
            if ($this->aspect_ratio_id === null) {
                $this->aspectRatioObject = null;
            } else {
                try {
                    $this->aspectRatioObject = new ImageAspectRatio();
                    $this->aspectRatioObject->load($this->aspect_ratio_id);
                } catch (NotFoundException $e) {
                    $this->aspectRatioObject = null;
                }
            }
        }

        return $this->aspectRatioObject;
    }

    /**
     * @return Image|null
     */
    public function getImage()
    {
        if ($this->imageObject === null) {
            if ($this->image_id === null) {
                $this->imageObject = null;
            } else {
                try {
                    $this->imageObject = new Image();
                    $this->imageObject->load($this->image_id);
                } catch (NotFoundException $e) {
                    $this->imageObject = null;
                }
            }
        }

        return $this->imageObject;
    }

    protected function afterSave()
    {
        ImageHelper::deleteVariations($this->image_id);
    }
}
