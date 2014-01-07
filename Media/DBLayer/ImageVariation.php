<?php

namespace WebFW\Framework\Media\DBLayer;

use WebFW\Framework\Core\Exceptions\NotFoundException;
use WebFW\Framework\Database\TableGateway;
use WebFW\Framework\Media\DBLayer\Tables\ImageVariation as ImageVariationT;

/**
 * Class ImageVariation
 * @package WebFW\Framework\Media
 *
 * @property $variation
 * @property $width
 * @property $height
 * @property $caption
 * @property $type
 * @property $aspect_ratio_id
 */
class ImageVariation extends TableGateway
{
    protected $aspectRatioObject = null;

    protected static $types = array(
        ImageVariationT::TYPE_CROPPED => 'Cropped',
        ImageVariationT::TYPE_MAX_WIDTH => 'Fixed width',
        ImageVariationT::TYPE_MAX_HEIGHT => 'Fixed height',
        ImageVariationT::TYPE_CONTAIN => 'Contain',
        ImageVariationT::TYPE_COVER => 'Cover',
    );

    public function __construct()
    {
        $this->setTable(ImageVariationT::getInstance());
        parent::__construct();
    }

    public function getCaption()
    {
        if ($this->caption !== null) {
            return $this->caption;
        }

        return $this->variation;
    }

    public function getAspectRatio()
    {
        if ($this->type === ImageVariationT::TYPE_MAX_WIDTH || $this->type === ImageVariationT::TYPE_MAX_HEIGHT) {
            return null;
        }

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

    public function getType()
    {
        return static::getTypeStatic($this->type);
    }

    public static function getTypes()
    {
        return static::$types;
    }

    public static function getTypeStatic($type)
    {
        if (array_key_exists($type, static::$types)) {
            return static::$types[$type];
        }

        return null;
    }

    protected function beforeSave()
    {
        if ($this->caption === '') {
            $this->caption = null;
        }

        if ($this->width === '') {
            $this->width = null;
        }

        if ($this->height === '') {
            $this->height = null;
        }

        if ($this->aspect_ratio_id === '') {
            $this->aspect_ratio_id = null;
        }
    }

    public function validateData()
    {
        $variationSet = '[a-zA-Z0-9_-]';
        if (preg_match('#^' . $variationSet . '*$#', $this->variation) !== 1) {
            $this->addValidationError('variation', 'Variation can contain symbols only from the set ' . $variationSet);
        }

        switch ($this->type) {
            case ImageVariationT::TYPE_CROPPED:
                if ($this->width <= 0) {
                    $this->addValidationError('width', 'Width must be set and positive');
                }
                if ($this->height <= 0) {
                    $this->addValidationError('height', 'Height must be set and positive');
                }
                break;
            case ImageVariationT::TYPE_MAX_WIDTH:
                if ($this->width <= 0) {
                    $this->addValidationError('width', 'Width must be set and positive');
                }
                if ($this->height !== null) {
                    $this->addValidationError('height', 'Height is not applicable for this variation type');
                }
                if ($this->aspect_ratio_id !== null) {
                    $this->addValidationError(
                        'aspect_ratio_id',
                        'Aspect ratio is not applicable for this variation type'
                    );
                }
                break;
            case ImageVariationT::TYPE_MAX_HEIGHT:
                if ($this->height <= 0) {
                    $this->addValidationError('height', 'Height must be set and positive');
                }
                if ($this->width !== null) {
                    $this->addValidationError('width', 'Width is not applicable for this variation type');
                }
                if ($this->aspect_ratio_id !== null) {
                    $this->addValidationError(
                        'aspect_ratio_id',
                        'Aspect ratio is not applicable for this variation type'
                    );
                }
                break;
            case ImageVariationT::TYPE_CONTAIN:
            case ImageVariationT::TYPE_COVER:
                if ($this->width <= 0) {
                    $this->addValidationError('width', 'Width must be set and positive');
                }
                if ($this->height <= 0) {
                    $this->addValidationError('height', 'Height must be set and positive');
                }
                if ($this->aspect_ratio_id !== null) {
                    $this->addValidationError(
                        'aspect_ratio_id',
                        'Aspect ratio is not applicable for this variation type'
                    );
                }
                break;
            default:
                $this->addValidationError('type', 'Invalid type specified');
                break;
        }
    }
}
