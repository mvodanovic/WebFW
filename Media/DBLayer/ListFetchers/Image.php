<?php

namespace WebFW\Framework\Media\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\Media\DBLayer\Tables\Image as ImageT;
use WebFW\Framework\Media\DBLayer\Image as ImageTG;

class Image extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(ImageT::getInstance());
        $this->setTableGateway(ImageTG::className());
        parent::__construct();
    }
}
