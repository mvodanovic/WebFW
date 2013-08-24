<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;

class UserType extends ListFetcher
{
    public function __construct()
    {
        $this->setTable('UserType', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        $this->setTableGateway('UserType', '\\WebFW\\CMS\\DBLayer\\');
        parent::__construct();
    }
}
