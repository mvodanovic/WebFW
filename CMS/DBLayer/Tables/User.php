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
    protected function init()
    {
        $this->setName('cms_user');

        $this->addColumn(IntegerColumn::spawn($this, 'user_id', false)->setDefaultValue(null, true));
        $this->addColumn(IntegerColumn::spawn($this, 'user_type_id', false));
        $this->addColumn(VarcharColumn::spawn($this, 'username', true, 50));
        $this->addColumn(VarcharColumn::spawn($this, 'email', false, 100));
        $this->addColumn(VarcharColumn::spawn($this, 'password_username', false, 64));
        $this->addColumn(VarcharColumn::spawn($this, 'password_email', false, 64));
        $this->addColumn(VarcharColumn::spawn($this, 'first_name', true, 100)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'last_name', true, 100)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'address', true, 200)->setDefaultValue(null));
        $this->addColumn(BooleanColumn::spawn($this, 'active', false)->setDefaultValue(false));

        $this->addConstraint(PrimaryKey::spawn($this)->addColumn($this->getColumn('user_id')));
        $this->addConstraint(ForeignKey::spawn(
            $this,
            ForeignKey::ACTION_CASCADE,
            ForeignKey::ACTION_RESTRICT,
            'fk_cms_user_user_type_id'
        )->addReference($this->getColumn('user_type_id'), UserType::getInstance()->getColumn('user_type_id')));
        $this->addConstraint(Unique::spawn($this)->addColumn($this->getColumn('username')));
        $this->addConstraint(Unique::spawn($this)->addColumn($this->getColumn('email')));
    }
}
