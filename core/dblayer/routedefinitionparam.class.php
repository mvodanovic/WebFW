<?php

namespace WebFW\Core\DBLayer;

use WebFW\Database\TableGateway;
use WebFW\Core\DBLayer\Tables\RouteDefinitionParam as TRouteDefinitionParam;

class RouteDefinitionParam extends TableGateway
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionParam::getInstance());
        parent::__construct();
    }
}
