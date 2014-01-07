<?php

namespace WebFW\Framework\Media\Controllers\CMS;

use WebFW\Framework\CMS\ListController;
use WebFW\Framework\Media\DBLayer\ListFetchers\ImageAspectRatio;
use WebFW\Framework\Media\DBLayer\ListFetchers\ImageVariation as LFImageVariation;
use WebFW\Framework\Media\DBLayer\ImageVariation as TGImageVariation;
use WebFW\Framework\Media\DBLayer\Tables\ImageVariation as TImageVariation;
use WebFW\Framework\Core\Classes\HTML\Input;
use WebFW\Framework\Core\Classes\HTML\Select;
use WebFW\Framework\CMS\Classes\EditTab;
use WebFW\Framework\CMS\Classes\ListHelper;

class ImageVariation extends ListController
{
    protected function init()
    {
        $this->pageTitle = 'CMS Users';

        parent::init();

        $this->listFetcher = new LFImageVariation();
        $this->tableGateway = new TGImageVariation();
    }

    protected function initList()
    {
        parent::initList();

        $this->sort = array(
            'variation' => 'ASC',
        );

        $this->addListColumn('variation', 'Variation');
        $this->addListColumn('strCaption', 'Caption');
        $this->addListColumn('width', 'Width');
        $this->addListColumn('height', 'Height');
        $this->addListColumn('strRatio', 'Ratio');
        $this->addListColumn('strType', 'Type');
        $this->addListColumn('strAspectRatio', 'Aspect Ratio');
    }

    protected function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $aspectRatioLM = new ImageAspectRatio();
        $aspectRatios = $aspectRatioLM->getList(null, array('aspect_ratio_id' => 'ASC'));

        $tab->addField(
            new Input('variation', Input::INPUT_TEXT),
            'Variation name',
            'Used as an unique identifier. Used also in image URLs.'
        );
        $tab->addField(
            new Input('caption', Input::INPUT_TEXT),
            'Caption',
            'Optional. A more user-friendly alternative to variation field.'
        );
        $tab->addField(
            new Input('width', Input::INPUT_TEXT),
            'Width',
            'Image width. Not used for fixed height type.'
        );
        $tab->addField(
            new Input('height', Input::INPUT_TEXT),
            'Height',
            'Image height. Not used for fixed width type.'
        );
        $tab->addField(
            new Select('type', ListHelper::getKeyValueListFromKeyValuePairs(TGImageVariation::getTypes())),
            'Type',
            'Cropped - a cropped part of an image; Scaled - image is scaled to fit the container, but not cropped;'
                . ' Fixed width / height - image is proportionally scaled to the desired dimension'
        );
        $tab->addField(
            new Select(
                'aspect_ratio_id',
                ListHelper::getKeyValueList($aspectRatios, 'aspect_ratio_id', 'caption', true)
            ),
            'Aspect ratio',
            'Used only for cropped variations to associate with a crop.'
        );

        $this->editTabs[] = $tab;
    }

    protected function initListFilters()
    {
        $aspectRatioLM = new ImageAspectRatio();
        $aspectRatios = $aspectRatioLM->getList(null, array('aspect_ratio_id' => 'ASC'));

        $this->addListFilter(
            new Select('type_id', ListHelper::getKeyValueListFromKeyValuePairs(TGImageVariation::getTypes(), true)),
            'Type'
        );

        $this->addListFilter(
            new Select(
                'aspect_ratio_id',
                ListHelper::getKeyValueList($aspectRatios, 'aspect_ratio_id', 'caption', true)
            ),
            'Aspect ratio'
        );
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            /** @var TGImageVariation $item */
            $aspectRatio = $item->getAspectRatio();
            $ratio = 'N/A';
            if ($item->type === TImageVariation::TYPE_CROPPED
                    || $item->type === TImageVariation::TYPE_CONTAIN
                    || $item->type === TImageVariation::TYPE_COVER) {
                $ratio = number_format($item->width / $item->height, 2, '.', '');
            }
            $item['strCaption'] = $item->getCaption();
            $item['strType'] = $item->getType();
            $item['strRatio'] = $ratio;
            $item['strAspectRatio'] = $aspectRatio !== null ? $aspectRatio->getCaption() : '';
        }
    }
}
