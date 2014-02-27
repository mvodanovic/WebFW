<?php

namespace mvodanovic\WebFW\CMS\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\CMS\DBLayer\Tables\User as UserT;
use mvodanovic\WebFW\CMS\DBLayer\User as UserTG;

class User extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UserT::getInstance());
        $this->setTableGateway(UserTG::className());
        parent::__construct();
    }
}
