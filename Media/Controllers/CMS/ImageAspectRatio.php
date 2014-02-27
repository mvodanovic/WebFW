<?php

namespace mvodanovic\WebFW\Media\Controllers\CMS;

use mvodanovic\WebFW\CMS\ListController;
use mvodanovic\WebFW\Media\DBLayer\ListFetchers\ImageAspectRatio as LFImageAspectRatio;
use mvodanovic\WebFW\Media\DBLayer\ImageAspectRatio as TGImageAspectRatio;
use mvodanovic\WebFW\Media\DBLayer\ListFetchers\ImageVariation;
use mvodanovic\WebFW\Media\DBLayer\Tables\ImageVariation as TImageVariation;
use mvodanovic\WebFW\Core\Classes\HTML\Input;
use mvodanovic\WebFW\Core\Classes\HTML\Select;
use mvodanovic\WebFW\CMS\Classes\EditTab;
use mvodanovic\WebFW\CMS\Classes\ListHelper;

class ImageAspectRatio extends ListController
{
    protected function init()
    {
        $this->pageTitle = 'CMS Users';

        parent::init();

        $this->listFetcher = new LFImageAspectRatio();
        $this->tableGateway = new TGImageAspectRatio();
    }

    protected function initList()
    {
        parent::initList();

        $this->sort = array(
            'aspect_ratio_id' => 'ASC',
        );

        $this->addListColumn('strCaption', 'Caption');
        $this->addListColumn('strRatio', 'Ratio');
        $this->addListColumn('strDefaultVariation', 'Default variation');
    }

    protected function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $variationLM = new ImageVariation();
        $variations = $variationLM->getList(
            array('type' => TImageVariation::TYPE_CROPPED),
            array('aspect_ratio_id' => 'ASC')
        );

        $tab->addField(
            new Input('caption', Input::INPUT_TEXT),
            'Caption',
            'Used as an unique identifier.'
        );
        $tab->addField(
            new Input('width', Input::INPUT_TEXT),
            'Width',
            'Ratio width.'
        );
        $tab->addField(
            new Input('height', Input::INPUT_TEXT),
            'Height',
            'Ratio height.'
        );
        $tab->addField(
            new Select('default_variation', ListHelper::getKeyValueList($variations, 'variation', 'variation', true)),
            'Default variation',
            'Variation used to represent the aspect ratio.'
        );

        $this->editTabs[] = $tab;
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            /** @var TGImageAspectRatio $item */
            $defaultVariation = $item->getDefaultVariation();
            $item['strCaption'] = $item->getCaption();
            $item['strRatio'] = $item->width  . ':' .  $item->height
                . ' (' . number_format($item->width / $item->height, 2, '.', '') . ')';
            $item['strDefaultVariation'] = $defaultVariation !== null ? $defaultVariation->getCaption() : '';
        }
    }
}
