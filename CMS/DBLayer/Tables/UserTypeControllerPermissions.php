<?php

namespace WebFW\Framework\CMS\DBLayer\Tables;

use WebFW\Framework\Database\Table;
use WebFW\Framework\Database\TableColumns\IntegerColumn;
use WebFW\Framework\Database\TableColumns\VarcharColumn;
use WebFW\Framework\Database\TableConstraints\PrimaryKey;
use WebFW\Framework\Database\TableConstraints\ForeignKey;

class UserTypeControllerPermissions extends Table
{
    protected function init()
    {
        $this->setName('cms_user_type_ctl_perms');

        $this->addColumn(IntegerColumn::spawn($this, 'user_type_id', false)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'controller', false, 150)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'permissions', false)->setDefaultValue(0));

        $this->addConstraint(PrimaryKey::spawn($this)
            ->addColumn($this->getColumn('user_type_id'))
            ->addColumn($this->getColumn('controller'))
        );
        $this->addConstraint(ForeignKey::spawn(
            $this,
            ForeignKey::ACTION_CASCADE,
            ForeignKey::ACTION_CASCADE
        )->addReference($this->getColumn('user_type_id'), UserType::getInstance()->getColumn('user_type_id')));
    }
}
