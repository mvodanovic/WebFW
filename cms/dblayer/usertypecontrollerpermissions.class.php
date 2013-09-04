<?php

namespace WebFW\CMS\DBLayer;

use WebFW\Database\TableGateway;

class UserTypeControllerPermissions extends TableGateway
{
    const TYPE_SELECT = 0x01;
    const TYPE_INSERT = 0x02;
    const TYPE_UPDATE = 0x04;
    const TYPE_DELETE = 0x08;
    const TYPE_RESERVED1 = 0x10;
    const TYPE_RESERVED2 = 0x20;
    const TYPE_RESERVED3 = 0x40;
    const TYPE_CUSTOM = 0x80;

    public function __construct()
    {
        $this->setTable('UserTypeControllerPermissions', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        parent::__construct();
    }
}
