<?php

namespace mvodanovic\WebFW\Core\DBLayer;

use mvodanovic\WebFW\Database\TableGateway;
use mvodanovic\WebFW\Core\DBLayer\Tables\RouteDefinitionParam as TRouteDefinitionParam;

class RouteDefinitionParam extends TableGateway
{
    public function __construct()
    {
        $this->setTable(TRouteDefinitionParam::getInstance());
        parent::__construct();
    }

    public function getCaption()
    {
        return $this->key;
    }
}
