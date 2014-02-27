<?php

namespace mvodanovic\WebFW\CMS\DBLayer\Tables;

use mvodanovic\WebFW\Database\Table;
use mvodanovic\WebFW\Database\TableColumns\IntegerColumn;
use mvodanovic\WebFW\Database\TableColumns\VarcharColumn;
use mvodanovic\WebFW\Database\TableColumns\BooleanColumn;
use mvodanovic\WebFW\Database\TableConstraints\PrimaryKey;
use mvodanovic\WebFW\Database\TableConstraints\ForeignKey;

class Navigation extends Table
{
    protected function init()
    {
        $this->setName('cms_navigation');

        $this->addColumn(IntegerColumn::spawn($this, 'node_id', false)->setDefaultValue(null, true));
        $this->addColumn(IntegerColumn::spawn($this, 'parent_node_id', true)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'order_id', false)->setDefaultValue(0));
        $this->addColumn(IntegerColumn::spawn($this, 'node_level', false)->setDefaultValue(0));
        $this->addColumn(VarcharColumn::spawn($this, 'caption', false, 50)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'controller', true, 150)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'action', true, 50)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'params', true, 500)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'custom_url', true, 500)->setDefaultValue(null));
        $this->addColumn(BooleanColumn::spawn($this, 'active', false)->setDefaultValue(true));

        $this->addConstraint(PrimaryKey::spawn($this)->addColumn($this->getColumn('node_id')));
        $this->addConstraint(ForeignKey::spawn(
            $this,
            ForeignKey::ACTION_CASCADE,
            ForeignKey::ACTION_RESTRICT
        )->addReference($this->getColumn('parent_node_id'), Navigation::getInstance()->getColumn('parent_node_id')));
    }
}
