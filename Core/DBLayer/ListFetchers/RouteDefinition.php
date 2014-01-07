<?php

namespace WebFW\Core\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\Core\DBLayer\Tables\RouteDefinition as TRouteDefinition;

class RouteDefinition extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinition::getInstance());
        $this->setTableGateway('RouteDefinition', '\\WebFW\\Core\\DBLayer\\');
        parent::__construct();
    }
}
