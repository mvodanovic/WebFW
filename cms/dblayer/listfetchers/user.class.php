<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\CMS\DBLayer\Tables\User as UserT;

class User extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UserT::getInstance());
        $this->setTableGateway('User', '\\WebFW\\CMS\\DBLayer\\');
        parent::__construct();
    }
}
