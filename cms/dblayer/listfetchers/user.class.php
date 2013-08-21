<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use \WebFW\Database\ListFetcher;

class User extends ListFetcher
{
    public function __construct()
    {
        $this->setTable('User', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        $this->setTableGateway('User', '\\WebFW\\CMS\\DBLayer\\');
        parent::__construct();
    }
}
