<?php

namespace WebFW\Core\DBLayer;

use WebFW\Database\TableGateway;

class RouteDefinitionRegex extends TableGateway
{
    public function __construct()
    {
        $this->setTable('RouteDefinitionRegex', '\\WebFW\\Core\\DBLayer\\Tables\\');
        parent::__construct();
    }
}
