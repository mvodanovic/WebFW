<?php

namespace WebFW\Framework\Media\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\Media\DBLayer\Tables\ImageAspectRatioCrop as ImageAspectRatioCropT;
use WebFW\Framework\Media\DBLayer\ImageAspectRatioCrop as ImageAspectRatioCropTG;

class ImageAspectRatioCrop extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageAspectRatioCropT::getInstance());
        $this->setTableGateway(ImageAspectRatioCropTG::className());
        parent::__construct();
    }
}
