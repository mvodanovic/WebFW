<?php

namespace mvodanovic\WebFW\Media\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Media\DBLayer\Tables\ImageVariation as ImageVariationT;
use mvodanovic\WebFW\Media\DBLayer\ImageVariation as ImageVariationTG;

class ImageVariation extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageVariationT::getInstance());
        $this->setTableGateway(ImageVariationTG::className());
        parent::__construct();
    }
}
