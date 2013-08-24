<?php

namespace WebFW\CMS\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;

class Navigation extends ListFetcher
{
    public function __construct()
    {
        $this->setTable('Navigation', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        $this->setTableGateway('Navigation', '\\WebFW\\CMS\\DBLayer\\');
        parent::__construct();
    }
}
