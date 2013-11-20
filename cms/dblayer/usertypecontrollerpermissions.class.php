<?php

namespace WebFW\CMS\DBLayer;

use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Database\TableGateway;
use WebFW\CMS\DBLayer\Tables\UserTypeControllerPermissions as UTCPT;

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

    protected $userTypeCaption = null;

    public function __construct()
    {
        $this->setTable(UTCPT::getInstance());
        parent::__construct();
    }

    public function getUserTypeCaption()
    {
        if ($this->userTypeCaption === null) {
            $userType = new UserType();
            try {
                $userType->load($this->user_type_id);
            } catch (NotFoundException $e) {}
            $this->userTypeCaption = $userType->caption;
        }

        return $this->userTypeCaption;
    }

    public function getControllerCaption()
    {
        return $this->controller;
    }

    public function checkTypePermissions($type)
    {
        return ($this->permissions & $type) === $type;
    }

    public function getCaption()
    {
        return $this->getUserTypeCaption() . ' - ' . $this->getControllerCaption();
    }
}
