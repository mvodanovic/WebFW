<?php

namespace WebFW\Framework\CMS\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\CMS\DBLayer\Tables\UserType as UserTypeT;
use WebFW\Framework\CMS\DBLayer\UserType as UserTypeTG;

class UserType extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UserTypeT::getInstance());
        $this->setTableGateway(UserTypeTG::className());
        parent::__construct();
    }
}
