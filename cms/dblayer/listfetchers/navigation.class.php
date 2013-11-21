<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\CMS\DBLayer\Tables\Navigation as NavigationT;
use WebFW\CMS\DBLayer\Navigation as NavigationTG;

class Navigation extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(NavigationT::getInstance());
        $this->setTableGateway(NavigationTG::className());
        parent::__construct();
    }
}
