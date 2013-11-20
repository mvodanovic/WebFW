<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\CMS\DBLayer\Tables\UserType as UserTypeT;

class UserType extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UserTypeT::getInstance());
        $this->setTableGateway('UserType', '\\WebFW\\CMS\\DBLayer\\');
        parent::__construct();
    }
}
