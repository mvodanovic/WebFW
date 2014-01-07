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
    protected function init()
    {
        $this->setName('webfw_route_definition');

        $this->addColumn(IntegerColumn::spawn($this, 'route_definition_id', false)->setDefaultValue(null, true));
        $this->addColumn(VarcharColumn::spawn($this, 'pattern', true, 200)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'controller', true, 50)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'namespace', true, 50)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'action', true, 50)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'order_id', false)->setDefaultValue(null));
        $this->addColumn(BooleanColumn::spawn($this, 'active', false)->setDefaultValue(true));

        $this->addConstraint(PrimaryKey::spawn($this)->addColumn($this->getColumn('route_definition_id')));
        $this->addConstraint(Unique::spawn($this)->addColumn($this->getColumn('pattern')));
    }
}
