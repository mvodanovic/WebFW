<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\CMS\DBLayer\Tables\UserType as UserTypeT;
use WebFW\CMS\DBLayer\UserType as UserTypeTG;

class UserType extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UserTypeT::getInstance());
        $this->setTableGateway(UserTypeTG::className());
        parent::__construct();
    }
}
