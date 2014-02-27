<?php

namespace mvodanovic\WebFW\Core\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Core\DBLayer\Tables\RouteDefinition as TRouteDefinition;
use mvodanovic\WebFW\Core\DBLayer\RouteDefinition as TGRouteDefinition;

class RouteDefinition extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinition::getInstance());
        $this->setTableGateway(TGRouteDefinition::className());
        parent::__construct();
    }
}
