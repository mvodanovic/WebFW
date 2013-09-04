<?php

namespace WebFW\CMS\DBLayer;

use WebFW\Database\TableGateway;

class UserTypeControllerPermissions extends TableGateway
{
    const TYPE_SELECT = 0x01;
    const TYPE_INSERT = 0x02;
    const TYPE_UPDATE = 0x04;
    const TYPE_DELETE = 0x08;

    public function __construct()
    {
        $this->setTable('UserTypeControllerPermissions', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        parent::__construct();
    }
}
