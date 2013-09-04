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
        $this->addColumn(new VarcharColumn('controller', false, 100));
        $this->addColumn(new VarcharColumn('namespace', false, 200));
        $this->addColumn(new IntegerColumn('permissions', false));

        $this->getColumn('user_type_id')->setDefaultValue(null);
        $this->getColumn('controller')->setDefaultValue(null);
        $this->getColumn('namespace')->setDefaultValue(null);
        $this->getColumn('permissions')->setDefaultValue(0);

        $this->addConstraint(new PrimaryKey('user_type_id', 'controller', 'namespace'));
        $this->addConstraint(new ForeignKey('user_type_id', 'cms_user_type.user_type_id', ForeignKey::ACTION_UPDATE, ForeignKey::ACTION_CASCADE));
    }
}
