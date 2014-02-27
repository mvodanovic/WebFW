<?php

namespace mvodanovic\WebFW\CMS\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\CMS\DBLayer\Tables\UserTypeControllerPermissions as UTCPT;
use mvodanovic\WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCPTG;

class UserTypeControllerPermissions extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UTCPT::getInstance());
        $this->setTableGateway(UTCPTG::className());
        parent::__construct();
    }
}
