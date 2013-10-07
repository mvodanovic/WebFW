<?php

namespace WebFW\Core\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;

class RouteDefinitionRegex extends ListFetcher
{
    public function __construct()
    {
        $this->setTable('RouteDefinitionRegex', '\\WebFW\\Core\\DBLayer\\Tables\\');
        $this->setTableGateway('RouteDefinitionRegex', '\\WebFW\\Core\\DBLayer\\');
        parent::__construct();
    }
}
