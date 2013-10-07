<?php

namespace WebFW\Core\DBLayer\Tables;

use WebFW\Database\Table;
use WebFW\Database\TableColumns\IntegerColumn;
use WebFW\Database\TableColumns\VarcharColumn;
use WebFW\Database\TableConstraints\PrimaryKey;
use WebFW\Database\TableConstraints\ForeignKey;
use WebFW\Database\TableConstraints\Unique;

class RouteDefinitionRegex extends Table
{
    public function __construct()
    {
        $this->setName('webfw_route_definition_regex');

        $this->addColumn(new IntegerColumn('item_id', false));
        $this->addColumn(new IntegerColumn('route_definition_id', false));
        $this->addColumn(new VarcharColumn('variable', false, 50));
        $this->addColumn(new VarcharColumn('regex', true, 200));

        $this->getColumn('item_id')->setDefaultValue(null, true);
        $this->getColumn('route_definition_id')->setDefaultValue(null);
        $this->getColumn('variable')->setDefaultValue(null);
        $this->getColumn('regex')->setDefaultValue(null);


        $this->addConstraint(new PrimaryKey('item_id'));
        $this->addConstraint(new ForeignKey(
            'webfw_route_definition',
            array('route_definition_id' => 'route_definition_id'),
            ForeignKey::ACTION_CASCADE,
            ForeignKey::ACTION_CASCADE
        ));
        $this->addConstraint(new Unique(array('route_definition_id', 'variable')));
    }
}
