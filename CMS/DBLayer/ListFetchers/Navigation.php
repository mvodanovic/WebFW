<?php

namespace mvodanovic\WebFW\CMS\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\CMS\DBLayer\Tables\Navigation as NavigationT;
use mvodanovic\WebFW\CMS\DBLayer\Navigation as NavigationTG;

class Navigation extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(NavigationT::getInstance());
        $this->setTableGateway(NavigationTG::className());
        parent::__construct();
    }
}
