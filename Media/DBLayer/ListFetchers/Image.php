<?php

namespace mvodanovic\WebFW\Media\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Media\DBLayer\Tables\Image as ImageT;
use mvodanovic\WebFW\Media\DBLayer\Image as ImageTG;

class Image extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageT::getInstance());
        $this->setTableGateway(ImageTG::className());
        parent::__construct();
    }
}
