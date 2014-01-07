<?php

namespace WebFW\Framework\CMS\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\CMS\DBLayer\Tables\UserTypeControllerPermissions as UTCPT;
use WebFW\Framework\CMS\DBLayer\UserTypeControllerPermissions as UTCPTG;

class UserTypeControllerPermissions extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(UTCPT::getInstance());
        $this->setTableGateway(UTCPTG::className());
        parent::__construct();
    }
}
