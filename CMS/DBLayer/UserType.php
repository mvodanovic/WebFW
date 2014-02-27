<?php

namespace mvodanovic\WebFW\CMS\DBLayer;

use mvodanovic\WebFW\Database\TableGateway;
use mvodanovic\WebFW\CMS\DBLayer\Tables\UserType as UserTypeT;

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
