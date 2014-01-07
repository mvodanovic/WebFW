<?php

namespace WebFW\Media\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\Media\DBLayer\Tables\ImageAspectRatioCrop as ImageAspectRatioCropT;
use WebFW\Media\DBLayer\ImageAspectRatioCrop as ImageAspectRatioCropTG;

class ImageAspectRatioCrop extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageAspectRatioCropT::getInstance());
        $this->setTableGateway(ImageAspectRatioCropTG::className());
        parent::__construct();
    }
}
