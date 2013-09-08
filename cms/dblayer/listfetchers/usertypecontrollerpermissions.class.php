<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;

class UserTypeControllerPermissions extends ListFetcher
{
    public function __construct()
    {
        $this->setTable('UserTypeControllerPermissions', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        $this->setTableGateway('UserTypeControllerPermissions', '\\WebFW\\CMS\\DBLayer\\');
        parent::__construct();
    }
}
