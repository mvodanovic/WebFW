<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\CMS\DBLayer\Tables\UserTypeControllerPermissions as UTCPT;

class UserTypeControllerPermissions extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UTCPT::getInstance());
        $this->setTableGateway('UserTypeControllerPermissions', '\\WebFW\\CMS\\DBLayer\\');
        parent::__construct();
    }
}
