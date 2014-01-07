<?php

namespace WebFW\Framework\Media\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\Media\DBLayer\Tables\ImageAspectRatio as ImageAspectRatioT;
use WebFW\Framework\Media\DBLayer\ImageAspectRatio as ImageAspectRatioTG;

class ImageAspectRatio extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageAspectRatioT::getInstance());
        $this->setTableGateway(ImageAspectRatioTG::className());
        parent::__construct();
    }
}
