<?php

namespace WebFW\CMS\DBLayer\Tables;

use WebFW\Database\Table;
use WebFW\Database\TableColumns\IntegerColumn;
use WebFW\Database\TableColumns\VarcharColumn;
use WebFW\Database\TableColumns\BooleanColumn;
use WebFW\Database\TableConstraints\PrimaryKey;
use WebFW\Database\TableConstraints\Unique;

class UserType extends Table
{
    public function __construct()
    {
        $this->setName('cms_user_type');

        $this->addColumn(new IntegerColumn('user_type_id', false));
        $this->addColumn(new VarcharColumn('caption', false, 100));
        $this->addColumn(new BooleanColumn('is_root', false));

        $this->getColumn('user_type_id')->setDefaultValue(null, true);

        $this->addConstraint(new PrimaryKey('user_type_id'));
        $this->addConstraint(new Unique('caption'));
    }
}
