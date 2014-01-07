<?php

namespace WebFW\Framework\CMS\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\CMS\DBLayer\Tables\Navigation as NavigationT;
use WebFW\Framework\CMS\DBLayer\Navigation as NavigationTG;

class Navigation extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(NavigationT::getInstance());
        $this->setTableGateway(NavigationTG::className());
        parent::__construct();
    }
}
