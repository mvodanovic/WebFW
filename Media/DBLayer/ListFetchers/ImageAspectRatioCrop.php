<?php

namespace mvodanovic\WebFW\Media\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Media\DBLayer\Tables\ImageAspectRatioCrop as ImageAspectRatioCropT;
use mvodanovic\WebFW\Media\DBLayer\ImageAspectRatioCrop as ImageAspectRatioCropTG;

class ImageAspectRatioCrop extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageAspectRatioCropT::getInstance());
        $this->setTableGateway(ImageAspectRatioCropTG::className());
        parent::__construct();
    }
}
