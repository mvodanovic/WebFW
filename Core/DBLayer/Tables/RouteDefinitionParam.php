<?php

namespace WebFW\Framework\Core\DBLayer\Tables;

use WebFW\Framework\Database\Table;
use WebFW\Framework\Database\TableColumns\IntegerColumn;
use WebFW\Framework\Database\TableColumns\VarcharColumn;
use WebFW\Framework\Database\TableConstraints\PrimaryKey;
use WebFW\Framework\Database\TableConstraints\ForeignKey;
use WebFW\Framework\Database\TableConstraints\Unique;

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
