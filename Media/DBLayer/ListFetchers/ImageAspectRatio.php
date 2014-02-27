<?php

namespace mvodanovic\WebFW\Media\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Media\DBLayer\Tables\ImageAspectRatio as ImageAspectRatioT;
use mvodanovic\WebFW\Media\DBLayer\ImageAspectRatio as ImageAspectRatioTG;

class ImageAspectRatio extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageAspectRatioT::getInstance());
        $this->setTableGateway(ImageAspectRatioTG::className());
        parent::__construct();
    }
}
