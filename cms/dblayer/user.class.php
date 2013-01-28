<?php

namespace WebFW\CMS\DBLayer;

use \WebFW\Database\TableGateway;
use \WebFW\CMS\Classes\LoggedUser;

class User extends TableGateway
{
    public function __construct()
    {
        $this->setTable('User', '\\WebFW\\CMS\\DBLayer\\Tables\\');
        parent::__construct();
    }

    public function setPassword($password)
    {
        $this->password_username = LoggedUser::generatePasswordHash($password, $this->username);
        $this->password_email = LoggedUser::generatePasswordHash($password, $this->email);
    }
}
