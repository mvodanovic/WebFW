<?php

namespace WebFW\CMS\DBLayer\Tables;

use \WebFW\Database\Table;
use \WebFW\Database\TableColumns\IntegerColumn;
use \WebFW\Database\TableColumns\VarcharColumn;
use \WebFW\Database\TableColumns\BooleanColumn;
use \WebFW\Database\TableConstraints\PrimaryKey;
use \WebFW\Database\TableConstraints\ForeignKey;

class Navigation extends Table
{
    public function __construct()
    {
        $this->setName('cms_navigation');

        $this->addColumn(new IntegerColumn('node_id', false));
        $this->addColumn(new IntegerColumn('parent_node_id', true));
        $this->addColumn(new IntegerColumn('order_id', false));
        $this->addColumn(new IntegerColumn('node_level', false));
        $this->addColumn(new VarcharColumn('caption', false, 50));
        $this->addColumn(new VarcharColumn('controller', true, 50));
        $this->addColumn(new VarcharColumn('namespace', true, 100));
        $this->addColumn(new VarcharColumn('action', true, 50));
        $this->addColumn(new VarcharColumn('params', true, 500));
        $this->addColumn(new VarcharColumn('custom_url', true, 500));
        $this->addColumn(new BooleanColumn('active', false));

        $this->getColumn('parent_node_id')->setDefaultValue(null);
        $this->getColumn('order_id')->setDefaultValue(0);
        $this->getColumn('node_level')->setDefaultValue(0);
        $this->getColumn('caption')->setDefaultValue('');
        $this->getColumn('controller')->setDefaultValue('');
        $this->getColumn('namespace')->setDefaultValue('');
        $this->getColumn('action')->setDefaultValue('');
        $this->getColumn('params')->setDefaultValue('');
        $this->getColumn('custom_url')->setDefaultValue('');
        $this->getColumn('active')->setDefaultValue(true);

        $this->addConstraint(new PrimaryKey('item_id'));
        $this->addConstraint(new ForeignKey('parent_node_id', 'cms_navigation.node_id', ForeignKey::ACTION_UPDATE, ForeignKey::ACTION_RESTRICT));
    }
}
