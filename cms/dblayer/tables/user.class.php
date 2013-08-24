<?php

namespace WebFW\CMS\DBLayer\Tables;

use WebFW\Database\Table;
use WebFW\Database\TableColumns\IntegerColumn;
use WebFW\Database\TableColumns\VarcharColumn;
use WebFW\Database\TableColumns\BooleanColumn;
use WebFW\Database\TableConstraints\PrimaryKey;
use WebFW\Database\TableConstraints\ForeignKey;
use WebFW\Database\TableConstraints\Unique;

class User extends Table
{
    public function __construct()
    {
        $this->setName('cms_user');

        $this->addColumn(new IntegerColumn('user_id', false));
        $this->addColumn(new IntegerColumn('user_type_id', false));
        $this->addColumn(new VarcharColumn('username', true, 50));
        $this->addColumn(new VarcharColumn('email', false, 100));
        $this->addColumn(new VarcharColumn('password_username', false, 64));
        $this->addColumn(new VarcharColumn('password_email', false, 64));
        $this->addColumn(new VarcharColumn('first_name', true, 100));
        $this->addColumn(new VarcharColumn('last_name', true, 100));
        $this->addColumn(new VarcharColumn('address', true, 200));
        $this->addColumn(new BooleanColumn('active', false));

        $this->getColumn('active')->setDefaultValue(false);
        $this->getColumn('first_name')->setDefaultValue(null);
        $this->getColumn('last_name')->setDefaultValue(null);
        $this->getColumn('address')->setDefaultValue(null);

        $this->addConstraint(new PrimaryKey('user_id'));
        $this->addConstraint(new ForeignKey('user_type_id', 'cms_user_type.type_id', ForeignKey::ACTION_UPDATE, ForeignKey::ACTION_RESTRICT));
        $this->addConstraint(new Unique('username'));
        $this->addConstraint(new Unique('email'));
    }
}
