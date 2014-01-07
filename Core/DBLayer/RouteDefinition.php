<?php

namespace WebFW\Framework\Core\DBLayer;

use WebFW\Framework\Database\TableGateway;
use WebFW\Framework\Core\DBLayer\Tables\RouteDefinition as TRouteDefinition;
use WebFW\Framework\Core\DBLayer\Tables\RouteDefinitionParam as TRouteDefinitionParam;
use WebFW\Framework\Core\DBLayer\Tables\RouteDefinitionRegex as TRouteDefinitionRegex;

class RouteDefinition extends TableGateway
{
    public function __construct()
    {
        $this->setTable(TRouteDefinition::getInstance());
        $this->addForeignListFetcher(
            'params',
            TRouteDefinitionParam::getInstance()->getConstraint('fk_webfw_route_def_param'),
            RouteDefinitionParam::className()
        );
        $this->addForeignListFetcher(
            'regexes',
            TRouteDefinitionRegex::getInstance()->getConstraint('fk_webfw_route_def_regex'),
            RouteDefinitionRegex::className()
        );
        parent::__construct();
    }

    public function getCaption()
    {
        return $this->pattern;
    }
}
