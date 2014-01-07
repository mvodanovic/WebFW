<?php

namespace WebFW\Framework\Core\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\Core\DBLayer\Tables\RouteDefinitionRegex as TRouteDefinitionRegex;
use WebFW\Framework\Core\DBLayer\RouteDefinitionRegex as TGRouteDefinitionRegex;

class RouteDefinitionRegex extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionRegex::getInstance());
        $this->setTableGateway(TGRouteDefinitionRegex::className());
        parent::__construct();
    }
}
