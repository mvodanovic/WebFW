<?php

namespace WebFW\Framework\Media\DBLayer;

use WebFW\Framework\CMS\Classes\EditTab;
use WebFW\Framework\Core\Router;
use WebFW\Framework\Database\TableGateway;
use WebFW\Framework\Media\Classes\ImageHelper;
use WebFW\Framework\Media\Controllers\InternalImage;
use WebFW\Framework\Media\DBLayer\Tables\Image as ImageT;

/**
 * Class Image
 * @package WebFW\Framework\Media\DBLayer
 *
 * @property $image_id
 * @property $image_type_id
 * @property $extension
 * @property $caption
 * @property $author
 * @property $uri
 * @property $height
 * @property $width
 */
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

    public function getURL($variation = null)
    {
        switch ($this->image_type_id) {
            case ImageT::TYPE_EXTERNAL:
                return $this->uri;
            case ImageT::TYPE_INTERNAL:
                $params = array(
                    'image_id' => $this->image_id,
                    'filename' => $this->getFileName(),
                );
                if ($variation !== null) {
                    $params['variation'] = $variation;
                }
                return Router::getInstance()->URL(InternalImage::className(), null, $params);
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
        $extension = 'jpg';

        switch ($this->extension) {
            case ImageT::EXT_PNG:
                $extension = 'png';
                break;
            case ImageT::EXT_GIF:
                $extension = 'gif';
                break;
        }

        return ImageHelper::getContentTypeByExtension($extension);
    }

    public static function getTypes()
    {
        return static::$imageTypes;
    }

    public function beforeSave()
    {
        $this->setImageSize();
    }

    public function validateData()
    {
        $this->validateUploadedImage();
    }

    public function afterSave()
    {
        $this->processUploadedImage();
    }

    protected function setImageSize()
    {
        $imageDef = null;
        if (array_key_exists(EditTab::FIELD_PREFIX . 'file', $_FILES)) {
            $imageDef = &$_FILES[EditTab::FIELD_PREFIX . 'file'];
        }

        if ($imageDef === null || $imageDef['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        if (!is_uploaded_file($imageDef['tmp_name'])) {
            return;
        }

        $imageSize = @getimagesize($imageDef['tmp_name']);
        if (!is_array($imageSize)) {
            return;
        }

        $this->width = $imageSize[0];
        $this->height = $imageSize[1];
    }

    protected function validateUploadedImage()
    {
        $imageDef = null;
        if (array_key_exists(EditTab::FIELD_PREFIX . 'file', $_FILES)) {
            $imageDef = &$_FILES[EditTab::FIELD_PREFIX . 'file'];
        }

        if ($imageDef === null) {
            return;
        }

        switch ($imageDef['error']) {
            case UPLOAD_ERR_OK:
            case UPLOAD_ERR_NO_FILE:
                return;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->addValidationError('file', 'Uploaded image too large (' . $imageDef['size'] . ' bytes)');
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->addValidationError('file', 'Image upload interrupted');
                break;
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->addValidationError('file', 'Error while uploading image');
                break;
        }

        $fInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fInfo->file($imageDef['tmp_name']);
        if (!in_array($mimeType, ImageHelper::getSupportedTypes())) {
            $this->addValidationError('file', 'Image type "' . $mimeType . '" not supported');
        }
    }

    protected function processUploadedImage()
    {
        $imageDef = null;
        if (array_key_exists(EditTab::FIELD_PREFIX . 'file', $_FILES)) {
            $imageDef = &$_FILES[EditTab::FIELD_PREFIX . 'file'];
        }

        if ($imageDef === null || $imageDef['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        if (!is_uploaded_file($imageDef['tmp_name'])) {
            return;
        }

        ImageHelper::saveImageToDisk($imageDef['tmp_name'], $this->image_id);
    }
}
