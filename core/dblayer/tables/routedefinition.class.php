<?php

namespace WebFW\Core\DBLayer\Tables;

use WebFW\Database\Table;
use WebFW\Database\TableColumns\IntegerColumn;
use WebFW\Database\TableColumns\VarcharColumn;
use WebFW\Database\TableColumns\BooleanColumn;
use WebFW\Database\TableConstraints\PrimaryKey;
use WebFW\Database\TableConstraints\Unique;

class RouteDefinition extends Table
{
    public function __construct()
    {
        $this->setName('webfw_route_definition');

        $this->addColumn(new IntegerColumn('route_definition_id', false));
        $this->addColumn(new VarcharColumn('pattern', true, 200));
        $this->addColumn(new VarcharColumn('controller', true, 50));
        $this->addColumn(new VarcharColumn('namespace', true, 50));
        $this->addColumn(new VarcharColumn('action', true, 50));
        $this->addColumn(new IntegerColumn('order_id', false));
        $this->addColumn(new BooleanColumn('active', false));

        $this->getColumn('route_definition_id')->setDefaultValue(null, true);
        $this->getColumn('pattern')->setDefaultValue(null);
        $this->getColumn('controller')->setDefaultValue(null);
        $this->getColumn('namespace')->setDefaultValue(null);
        $this->getColumn('action')->setDefaultValue(null);
        $this->getColumn('order_id')->setDefaultValue(null);
        $this->getColumn('active')->setDefaultValue(true);


        $this->addConstraint(new PrimaryKey('route_definition_id'));
        $this->addConstraint(new Unique('pattern'));
    }
}
