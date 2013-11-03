<?php

namespace WebFW\Core\DBLayer\ListFetchers;

use WebFW\Database\ListFetcher;
use WebFW\Core\DBLayer\Tables\RouteDefinitionParam as TRouteDefinitionParam;

class RouteDefinitionParam extends ListFetcher
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionParam::getInstance());
        $this->setTableGateway('RouteDefinitionParam', '\\WebFW\\Core\\DBLayer\\');
        parent::__construct();
    }
}
