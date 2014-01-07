<?php

namespace WebFW\Framework\Core\DBLayer;

use WebFW\Framework\Database\TableGateway;
use WebFW\Framework\Core\DBLayer\Tables\RouteDefinitionParam as TRouteDefinitionParam;

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
