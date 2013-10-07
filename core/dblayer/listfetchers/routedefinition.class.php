<?php

namespace WebFW\Core\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;

class RouteDefinition extends ListFetcher
{
    public function __construct()
    {
        $this->setTable('RouteDefinition', '\\WebFW\\Core\\DBLayer\\Tables\\');
        $this->setTableGateway('RouteDefinition', '\\WebFW\\Core\\DBLayer\\');
        parent::__construct();
    }
}
