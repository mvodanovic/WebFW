<?php

namespace WebFW\Framework\CMS\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\CMS\DBLayer\Tables\User as UserT;
use WebFW\Framework\CMS\DBLayer\User as UserTG;

class User extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UserT::getInstance());
        $this->setTableGateway(UserTG::className());
        parent::__construct();
    }
}
