<?php

namespace WebFW\Media\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\Media\DBLayer\Tables\ImageVariation as ImageVariationT;
use WebFW\Media\DBLayer\ImageVariation as ImageVariationTG;

class ImageVariation extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageVariationT::getInstance());
        $this->setTableGateway(ImageVariationTG::className());
        parent::__construct();
    }
}
