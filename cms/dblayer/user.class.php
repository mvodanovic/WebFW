<?php

namespace WebFW\CMS\DBLayer;

use \WebFW\Core\Exception;
use \WebFW\Database\TableGateway;
use \WebFW\CMS\Classes\LoggedUser;

class User extends TableGateway
{
    protected $userType = null;

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

    public function getUserType()
    {
        if ($this->userType === null) {
            $userType = new UserType();
            try {
                $userType->load($this->user_type_id);
                $this->userType = $userType->caption;
            } catch (Exception $e) {}
        }

        return $this->userType;
    }

    public function getFullName()
    {
        return implode(' ', array($this->firstName, $this->lastName));
    }
}
