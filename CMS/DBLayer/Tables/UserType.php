<?php

namespace mvodanovic\WebFW\CMS\DBLayer\Tables;

use mvodanovic\WebFW\Database\Table;
use mvodanovic\WebFW\Database\TableColumns\IntegerColumn;
use mvodanovic\WebFW\Database\TableColumns\VarcharColumn;
use mvodanovic\WebFW\Database\TableColumns\BooleanColumn;
use mvodanovic\WebFW\Database\TableConstraints\PrimaryKey;
use mvodanovic\WebFW\Database\TableConstraints\Unique;

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
