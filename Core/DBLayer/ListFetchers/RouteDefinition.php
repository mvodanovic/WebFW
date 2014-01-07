<?php

namespace WebFW\Framework\Core\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\Core\DBLayer\Tables\RouteDefinition as TRouteDefinition;
use WebFW\Framework\Core\DBLayer\RouteDefinition as TGRouteDefinition;

class RouteDefinition extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinition::getInstance());
        $this->setTableGateway(TGRouteDefinition::className());
        parent::__construct();
    }
}
