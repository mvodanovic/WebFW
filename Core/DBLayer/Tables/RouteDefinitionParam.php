<?php

namespace mvodanovic\WebFW\Core\DBLayer\Tables;

use mvodanovic\WebFW\Database\Table;
use mvodanovic\WebFW\Database\TableColumns\IntegerColumn;
use mvodanovic\WebFW\Database\TableColumns\VarcharColumn;
use mvodanovic\WebFW\Database\TableConstraints\PrimaryKey;
use mvodanovic\WebFW\Database\TableConstraints\ForeignKey;
use mvodanovic\WebFW\Database\TableConstraints\Unique;

class RouteDefinitionParam extends Table
{
    protected function init()
    {
        $this->setName('webfw_route_definition_param');

        $this->addColumn(IntegerColumn::spawn($this, 'item_id', false)->setDefaultValue(null, true));
        $this->addColumn(IntegerColumn::spawn($this, 'route_definition_id', false)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'key', false, 200)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'value', true, 500)->setDefaultValue(null));

        $this->addConstraint(PrimaryKey::spawn($this)->addColumn($this->getColumn('item_id')));
        $this->addConstraint(ForeignKey::spawn(
                $this,
                ForeignKey::ACTION_CASCADE,
                ForeignKey::ACTION_CASCADE,
                'fk_webfw_route_def_param'
            )->addReference(
                $this->getColumn('route_definition_id'),
                RouteDefinition::getInstance()->getColumn('route_definition_id'))
        );
        $this->addConstraint(Unique::spawn($this)
            ->addColumn($this->getColumn('route_definition_id'))->addColumn($this->getColumn('key')));
    }
}
