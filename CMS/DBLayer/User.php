<?php

namespace mvodanovic\WebFW\CMS\DBLayer;

use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use mvodanovic\WebFW\Database\TableGateway;
use mvodanovic\WebFW\CMS\DBLayer\Tables\User as UserT;
use mvodanovic\WebFW\CMS\Classes\LoggedUser;

class User extends TableGateway
{
    protected $userType = null;

    public function __construct()
    {
        $this->setTable(UserT::getInstance());
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
            } catch (NotFoundException $e) {}
        }

        return $this->userType;
    }

    public function getFullName()
    {
        return implode(' ', array($this->firstName, $this->lastName));
    }

    public function getCaption()
    {
        return $this->username === null ? $this->email : $this->username;
    }
}
