<?php

namespace WebFW\Framework\Media\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\Media\DBLayer\Tables\ImageVariation as ImageVariationT;
use WebFW\Framework\Media\DBLayer\ImageVariation as ImageVariationTG;

class ImageVariation extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageVariationT::getInstance());
        $this->setTableGateway(ImageVariationTG::className());
        parent::__construct();
    }
}
