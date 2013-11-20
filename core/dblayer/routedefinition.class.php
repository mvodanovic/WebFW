<?php

namespace WebFW\Core\DBLayer;

use WebFW\Database\TableGateway;
use WebFW\Core\DBLayer\Tables\RouteDefinition as TRouteDefinition;
use WebFW\Core\DBLayer\Tables\RouteDefinitionParam as TRouteDefinitionParam;
use WebFW\Core\DBLayer\Tables\RouteDefinitionRegex as TRouteDefinitionRegex;

class RouteDefinition extends TableGateway
{
    public function __construct()
    {
        $this->setTable(TRouteDefinition::getInstance());
        $this->addForeignListFetcher(
            'params',
            TRouteDefinitionParam::getInstance()->getConstraint('fk_webfw_route_def_param'),
            'RouteDefinitionParam',
            '\\WebFW\\Core\\DBLayer\\ListFetchers\\'
        );
        $this->addForeignListFetcher(
            'regexes',
            TRouteDefinitionRegex::getInstance()->getConstraint('fk_webfw_route_def_regex'),
            'RouteDefinitionRegex',
            '\\WebFW\\Core\\DBLayer\\ListFetchers\\'
        );
        parent::__construct();
    }

    public function getCaption()
    {
        return $this->pattern;
    }
}
