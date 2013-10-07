<?php

namespace WebFW\Core\DBLayer;

use WebFW\Database\TableGateway;

class RouteDefinition extends TableGateway
{
    public function __construct()
    {
        $this->setTable('RouteDefinition', '\\WebFW\\Core\\DBLayer\\Tables\\');
        $this->addForeignListFetcher(
            'params',
            array('route_definition_id'),
            'RouteDefinitionParam',
            '\\WebFW\\Core\\DBLayer\\ListFetchers\\'
        );
        $this->addForeignListFetcher(
            'regexes',
            array('route_definition_id'),
            'RouteDefinitionRegex',
            '\\WebFW\\Core\\DBLayer\\ListFetchers\\'
        );
        parent::__construct();
    }
}
