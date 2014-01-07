<?php

namespace WebFW\Framework\Core\DBLayer\ListFetchers;

use WebFW\Framework\Database\ListFetcher;
use WebFW\Framework\Core\DBLayer\Tables\RouteDefinitionParam as TRouteDefinitionParam;
use WebFW\Framework\Core\DBLayer\RouteDefinitionParam as TGRouteDefinitionParam;

class RouteDefinitionParam extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionParam::getInstance());
        $this->setTableGateway(TGRouteDefinitionParam::className());
        parent::__construct();
    }
}
