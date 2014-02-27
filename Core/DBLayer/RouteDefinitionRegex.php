<?php

namespace mvodanovic\WebFW\Core\DBLayer;

use mvodanovic\WebFW\Database\TableGateway;
use mvodanovic\WebFW\Core\DBLayer\Tables\RouteDefinitionRegex as TRouteDefinitionRegex;

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
