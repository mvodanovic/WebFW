<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\CMS\DBLayer\Tables\Navigation as NavigationT;

class Navigation extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(NavigationT::getInstance());
        $this->setTableGateway('Navigation', '\\WebFW\\CMS\\DBLayer\\');
        parent::__construct();
    }
}
