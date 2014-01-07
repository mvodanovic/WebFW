<?php

namespace WebFW\Framework\CMS\DBLayer;

use WebFW\Framework\Database\TableGateway;
use WebFW\Framework\CMS\DBLayer\Tables\UserType as UserTypeT;

class UserType extends TableGateway
{
    public function __construct()
    {
        $this->setTable(UserTypeT::getInstance());
        parent::__construct();
    }

    public function getCaption()
    {
        return $this->caption;
    }
}
