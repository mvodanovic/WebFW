<?php

namespace WebFW\Core\DBLayer;

use WebFW\Database\TableGateway;

class RouteDefinitionParam extends TableGateway
{
    public function __construct()
    {
        $this->setTable('RouteDefinitionParam', '\\WebFW\\Core\\DBLayer\\Tables\\');
        parent::__construct();
    }
}
