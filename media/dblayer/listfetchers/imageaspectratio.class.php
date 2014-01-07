<?php

namespace WebFW\Media\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\Media\DBLayer\Tables\ImageAspectRatio as ImageAspectRatioT;
use WebFW\Media\DBLayer\ImageAspectRatio as ImageAspectRatioTG;

class ImageAspectRatio extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageAspectRatioT::getInstance());
        $this->setTableGateway(ImageAspectRatioTG::className());
        parent::__construct();
    }
}
