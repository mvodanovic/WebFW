<?php

namespace WebFW\CMS\DBLayer;

use WebFW\Database\TableGateway;

class UserType extends TableGateway
{
    public function __construct()
    {
        $this->setTable('UserType', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        parent::__construct();
    }
}
