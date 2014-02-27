<?php

namespace mvodanovic\WebFW\Core\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Core\DBLayer\Tables\RouteDefinitionRegex as TRouteDefinitionRegex;
use mvodanovic\WebFW\Core\DBLayer\RouteDefinitionRegex as TGRouteDefinitionRegex;

class RouteDefinitionRegex extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionRegex::getInstance());
        $this->setTableGateway(TGRouteDefinitionRegex::className());
        parent::__construct();
    }
}
