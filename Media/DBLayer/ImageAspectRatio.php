<?php

namespace mvodanovic\WebFW\Media\DBLayer;

use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use mvodanovic\WebFW\Database\TableGateway;
use mvodanovic\WebFW\Media\DBLayer\Tables\ImageAspectRatio as ImageAspectRatioT;

/**
 * Class ImageAspectRatio
 * @package mvodanovic\WebFW
 *
 * @property $aspect_ratio_id
 * @property $caption
 * @property $width
 * @property $height
 * @property $default_variation
 */
class ImageAspectRatio extends TableGateway
{
    protected $variationObject = null;

    public function __construct()
    {
        $this->setTable(ImageAspectRatioT::getInstance());
        parent::__construct();
    }

    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @return ImageVariation|null
     */
    public function getDefaultVariation()
    {
        if ($this->variationObject === null) {
            if ($this->default_variation === null) {
                $this->variationObject = null;
            } else {
                try {
                    $this->variationObject = new ImageVariation();
                    $this->variationObject->load($this->default_variation);
                } catch (NotFoundException $e) {
                    $this->variationObject = null;
                }
            }
        }

        return $this->variationObject;
    }
}
