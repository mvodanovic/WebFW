<?php

namespace mvodanovic\WebFW\Media\Controllers\CMS;

use mvodanovic\WebFW\CMS\Classes\ListRowAction;
use mvodanovic\WebFW\CMS\Classes\PermissionsHelper;
use mvodanovic\WebFW\CMS\ListController;
use mvodanovic\WebFW\Core\Classes\HTML\Link;
use mvodanovic\WebFW\Core\Classes\HTML\Message;
use mvodanovic\WebFW\Core\Config;
use mvodanovic\WebFW\Core\Exception;
use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use mvodanovic\WebFW\Core\Request;
use mvodanovic\WebFW\Media\DBLayer\ImageAspectRatioCrop;
use mvodanovic\WebFW\Media\DBLayer\ImageVariation;
use mvodanovic\WebFW\Media\DBLayer\ListFetchers\Image as LFImage;
use mvodanovic\WebFW\Media\DBLayer\ListFetchers\ImageAspectRatio;
use mvodanovic\WebFW\Media\DBLayer\Tables\Image as TImage;
use mvodanovic\WebFW\Media\DBLayer\Tables\ImageVariation as TImageVariation;
use mvodanovic\WebFW\Media\DBLayer\Image as TGImage;
use mvodanovic\WebFW\Core\Classes\HTML\Input;
use mvodanovic\WebFW\Core\Classes\HTML\Select;
use mvodanovic\WebFW\CMS\Classes\EditTab;
use mvodanovic\WebFW\CMS\Classes\ListHelper;
use mvodanovic\WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;

class Image extends ListController
{
    /** @var ImageVariation|null */
    protected $variationObject;

    const MAX_CROP_EDITOR_WIDTH = 640;
    const MAX_CROP_EDITOR_HEIGHT = 480;
    const MAX_CROP_THUMBNAIL_WIDTH = 180;
    const MAX_CROP_THUMBNAIL_HEIGHT = 200;

    protected function init()
    {
        $this->pageTitle = 'CMS Users';

        parent::init();

        $this->listFetcher = new LFImage();
        $this->tableGateway = new TGImage();
    }

    protected function initList()
    {
        parent::initList();
        $this->template = 'images.list';
        $this->templateDirectory = \mvodanovic\WebFW\Core\FW_PATH . '/Media/Templates/CMS';

        $variation = Config::get('General', 'cmsImageVariation');
        $this->variationObject = new ImageVariation();
        try {
            $this->variationObject->load($variation);
            if ($this->variationObject->type !== TImageVariation::TYPE_COVER) {
                $this->variationObject = null;
                $this->addMessage(new Message(
                    'Variation "' . $variation . '" must be of type "'
                    . ImageVariation::getTypeStatic(TImageVariation::TYPE_COVER) . '"',
                    Message::TYPE_ERROR
                ));
            }
        } catch (NotFoundException $e) {
            $this->variationObject = null;
            $this->addMessage(new Message('Variation "' . $variation . '" missing', Message::TYPE_ERROR));
        }

        $this->sort = array(
            'image_id' => 'DESC',
        );
    }

    protected function initListRowActions()
    {
        /// Crop
        if (PermissionsHelper::checkForController($this, UTCP::TYPE_UPDATE)) {
            $options = array(
                'icons' => array('primary' => 'ui-icon-scissors'),
                'text' => false,
            );
            $link = new Link(null, null, $options);
            $route = $this->getRoute('showCropper');
            $listRowAction = new ListRowAction($link, $route);
            $listRowAction->setHandlerFunction('listRowHandlerCrop');
            $this->registerListRowAction($listRowAction);
        }

        parent::initListRowActions();
    }

    public function listRowHandlerCrop(TGImage $item)
    {
        if ($item->image_type_id !== TImage::TYPE_INTERNAL) {
            return null;
        }

        return $item->getPrimaryKeyValues();
    }

    protected function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $tab->addField(
            new Select('image_type_id', ListHelper::getKeyValueListFromKeyValuePairs(TGImage::getTypes())),
            'Image Type',
            'Internal - stored on local server; External - stored on an external service.'
        );

        $tab->addField(
            new Input('caption', Input::INPUT_TEXT),
            'Caption',
            'Caption to display next to the image.'
        );
        $tab->addField(
            new Input('author', Input::INPUT_TEXT),
            'Author',
            'Image author or copyright owner, if any.'
        );
        $tab->addField(
            new Input('uri', Input::INPUT_TEXT),
            'External URL',
            'URL of external image.'
        );
        $tab->addField(
            new Input('file', Input::INPUT_FILE),
            'Internal image file',
            'File to be used as an internal image.'
        );

        $this->editTabs[] = $tab;
    }

    public function showCropper()
    {
        $this->init();
        $this->addLinkedJS('/Static/JS/WebFW/jquery.Jcrop.min.js');
        $this->addLinkedJS('/Static/JS/WebFW/imagecropper.class.js');
        $this->addLinkedCSS('/Static/CSS/WebFW/jquery.Jcrop.min.css');
        $this->template = 'images.cropper';
        $this->templateDirectory = \mvodanovic\WebFW\Core\FW_PATH . '/Media/Templates/CMS';
        $image = new TGImage();
        $image->loadBy($this->getPrimaryKeyValues(false));
        $LFAspectRatio = new ImageAspectRatio();
        $aspectRatios = $LFAspectRatio->getList();
        $this->setTplVar('image', $image);
        $this->setTplVar('aspectRatios', $aspectRatios);

        foreach ($this->retrieveValidationErrors() as $fieldName => $errorGroup) {
            if (is_array($errorGroup)) {
                foreach ($errorGroup as $error) {
                    $this->addMessage(new Message($fieldName . ': ' . $error, Message::TYPE_ERROR));
                }
            } else {
                $this->addMessage(new Message($errorGroup, Message::TYPE_ERROR));
            }
        }
    }

    public function cropItem()
    {
        $isAjax = Request::getInstance()->ajax == '1';

        if ($isAjax) {
            $this->useTemplate = false;
            $this->simpleOutput = true;
            header('Content-type: application/json; charset=UTF-8');
        }

        $values = $this->getEditRequestValues();
        if (!array_key_exists('image_id', $values)
                || !array_key_exists('aspect_ratio_id', $values)
                || !array_key_exists('x', $values)
                || !array_key_exists('y', $values)
                || !array_key_exists('f', $values)) {
            if ($isAjax) {
                echo json_encode(array(
                    'status' => 'ERR',
                    'statusText' => 'Invalid request',
                ), JSON_FORCE_OBJECT);
                return;
            } else {
                $this->storeValidationErrors(array('Invalid request'));
                $this->setRedirectUrl($this->getURL('showCropper'), true);
            }
        }

        $cropObject = new ImageAspectRatioCrop();
        try {
            $cropObject->loadBy(array(
                'image_id' => $values['image_id'],
                'aspect_ratio_id' => $values['aspect_ratio_id'],
            ));
        } catch (NotFoundException $e) {
            $cropObject->image_id = $values['image_id'];
            $cropObject->aspect_ratio_id = $values['aspect_ratio_id'];
        }
        $cropObject->x = $values['x'];
        $cropObject->y = $values['y'];
        $cropObject->ratio_factor = $values['f'];
        $cropObject->save();

        if ($cropObject->hasValidationErrors()) {
            if ($isAjax) {
                $errorMessage = array();
                foreach ($cropObject->getValidationErrors() as $fieldName => $errorGroup) {
                    foreach ($errorGroup as $error) {
                        $errorMessage[] = $fieldName . ': ' . $error;
                    }
                }
                echo json_encode(array(
                    'status' => 'ERR',
                    'statusText' => implode("\n", $errorMessage),
                ), JSON_FORCE_OBJECT);
                return;
            } else {
                $this->storeValidationErrors($cropObject->getValidationErrors());
                $this->setRedirectUrl($this->getURL('showCropper'), true);
            }
        }

        if ($isAjax) {
            echo json_encode(array(
                'status' => 'OK',
                'data' => $this->getEditRequestValues(),
            ), JSON_FORCE_OBJECT);
        } else {
            $this->setRedirectUrl($this->getURL('showCropper'));
        }
    }

    /**
     * @return ImageVariation|null
     */
    public function getVariationObject()
    {
        return $this->variationObject;
    }

    public function getCropThumbnailWidth(ImageVariation $variation)
    {
        $width = $variation->width;
        if ($width > static::MAX_CROP_THUMBNAIL_WIDTH) {
            $width = static::MAX_CROP_THUMBNAIL_WIDTH;
        }

        $factor = $width / $variation->width;
        if ($variation->height * $factor > static::MAX_CROP_THUMBNAIL_HEIGHT) {
            $width = round($variation->width * static::MAX_CROP_THUMBNAIL_HEIGHT / $variation->height);
        }

        return $width;
    }

    public function getCropThumbnailHeight(ImageVariation $variation)
    {
        $height = $variation->height;
        if ($height > static::MAX_CROP_THUMBNAIL_HEIGHT) {
            $height = static::MAX_CROP_THUMBNAIL_HEIGHT;
        }

        $factor = $height / $variation->height;
        if ($variation->width * $factor > static::MAX_CROP_THUMBNAIL_WIDTH) {
            $height = round($variation->height * static::MAX_CROP_THUMBNAIL_WIDTH / $variation->width);
        }

        return $height;
    }
}
