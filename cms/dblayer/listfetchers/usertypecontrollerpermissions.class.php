<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\CMS\DBLayer\Tables\UserTypeControllerPermissions as UTCPT;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCPTG;

class UserTypeControllerPermissions extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UTCPT::getInstance());
        $this->setTableGateway(UTCPTG::className());
        parent::__construct();
    }
}
