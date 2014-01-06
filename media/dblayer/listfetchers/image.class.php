<?php

namespace WebFW\Media\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\Media\DBLayer\Tables\Image as ImageT;
use WebFW\Media\DBLayer\Image as ImageTG;

class Image extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageT::getInstance());
        $this->setTableGateway(ImageTG::className());
        parent::__construct();
    }
}
