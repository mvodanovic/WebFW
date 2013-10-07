<?php

namespace WebFW\Core\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;

class RouteDefinitionParam extends ListFetcher
{
    public function __construct()
    {
        $this->setTable('RouteDefinitionParam', '\\WebFW\\Core\\DBLayer\\Tables\\');
        $this->setTableGateway('RouteDefinitionParam', '\\WebFW\\Core\\DBLayer\\');
        parent::__construct();
    }
}
