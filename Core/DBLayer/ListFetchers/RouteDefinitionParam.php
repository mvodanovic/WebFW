<?php

namespace mvodanovic\WebFW\Core\DBLayer\ListFetchers;

use mvodanovic\WebFW\Database\ListFetcher;
use mvodanovic\WebFW\Core\DBLayer\Tables\RouteDefinitionParam as TRouteDefinitionParam;
use mvodanovic\WebFW\Core\DBLayer\RouteDefinitionParam as TGRouteDefinitionParam;

class RouteDefinitionParam extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionParam::getInstance());
        $this->setTableGateway(TGRouteDefinitionParam::className());
        parent::__construct();
    }
}
