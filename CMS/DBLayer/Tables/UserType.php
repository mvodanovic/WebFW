<?php

namespace WebFW\Framework\CMS\DBLayer\Tables;

use WebFW\Framework\Database\Table;
use WebFW\Framework\Database\TableColumns\IntegerColumn;
use WebFW\Framework\Database\TableColumns\VarcharColumn;
use WebFW\Framework\Database\TableColumns\BooleanColumn;
use WebFW\Framework\Database\TableConstraints\PrimaryKey;
use WebFW\Framework\Database\TableConstraints\Unique;

class UserType extends Table
{
    protected function init()
    {
        $this->setName('cms_user_type');

        $this->addColumn(IntegerColumn::spawn($this, 'user_type_id', false)->setDefaultValue(null, true));
        $this->addColumn(VarcharColumn::spawn($this, 'caption', false, 100));
        $this->addColumn(BooleanColumn::spawn($this, 'is_root', false));

        $this->addConstraint(PrimaryKey::spawn($this)->addColumn($this->getColumn('user_type_id')));
        $this->addConstraint(Unique::spawn($this)->addColumn($this->getColumn('caption')));
    }
}
