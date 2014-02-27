<?php

namespace mvodanovic\WebFW\CMS\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\CMS\DBLayer\Tables\UserType as UserTypeT;
use mvodanovic\WebFW\CMS\DBLayer\UserType as UserTypeTG;

class UserType extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UserTypeT::getInstance());
        $this->setTableGateway(UserTypeTG::className());
        parent::__construct();
    }
}
