<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\CMS\DBLayer\Tables\User as UserT;
use WebFW\CMS\DBLayer\User as UserTG;

class User extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UserT::getInstance());
        $this->setTableGateway(UserTG::className());
        parent::__construct();
    }
}
