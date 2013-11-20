<?php

namespace WebFW\Core\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\Core\DBLayer\Tables\RouteDefinitionRegex as TRouteDefinitionRegex;

class RouteDefinitionRegex extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionRegex::getInstance());
        $this->setTableGateway('RouteDefinitionRegex', '\\WebFW\\Core\\DBLayer\\');
        parent::__construct();
    }
}
