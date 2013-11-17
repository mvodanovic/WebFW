<?php

namespace WebFW\CMS\DBLayer\Tables;

use WebFW\Database\Table;
use WebFW\Database\TableColumns\IntegerColumn;
use WebFW\Database\TableColumns\VarcharColumn;
use WebFW\Database\TableConstraints\PrimaryKey;
use WebFW\Database\TableConstraints\ForeignKey;

class UserTypeControllerPermissions extends Table
{
    public function __construct()
    {
        $this->setName('cms_user_type_ctl_perms');

        $this->addColumn(new IntegerColumn('user_type_id', false));
        $this->addColumn(new VarcharColumn('controller', false, 150));
        $this->addColumn(new IntegerColumn('permissions', false));

        $this->getColumn('user_type_id')->setDefaultValue(null);
        $this->getColumn('controller')->setDefaultValue(null);
        $this->getColumn('permissions')->setDefaultValue(0);

        $this->addConstraint(new PrimaryKey(array('user_type_id', 'controller')));
        $this->addConstraint(new ForeignKey(
            'cms_user_type',
            array('user_type_id' => 'user_type_id'),
            ForeignKey::ACTION_CASCADE,
            ForeignKey::ACTION_CASCADE
        ));
    }
}
