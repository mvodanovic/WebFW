<?php

namespace WebFW\Framework\Core\DBLayer;

use WebFW\Framework\Database\TableGateway;
use WebFW\Framework\Core\DBLayer\Tables\RouteDefinitionRegex as TRouteDefinitionRegex;

class RouteDefinitionRegex extends TableGateway
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionRegex::getInstance());
        parent::__construct();
    }

    public function getCaption()
    {
        return $this->variable;
    }
}
