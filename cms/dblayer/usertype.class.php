<?php

namespace WebFW\CMS\DBLayer;

use WebFW\Database\TableGateway;
use WebFW\CMS\DBLayer\Tables\UserType as UserTypeT;

class UserType extends TableGateway
{
    public function __construct()
    {
        $this->setTable(UserTypeT::getInstance());
        parent::__construct();
    }
}
