<?php

namespace WebFW\Framework\CMS\Classes;

use WebFW\Framework\CMS\Controller;
use WebFW\Framework\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use WebFW\Framework\Core\Exceptions\NotFoundException;

class PermissionsHelper
{
    public static function checkForController(Controller $controller, $actionType)
    {
        return static::checkForControllerByName($controller->className(), $actionType);
    }

    public static function checkForControllerByName($ctl, $actionType)
    {
        $isOk = LoggedUser::isLoggedIn();

        /// Root users have all access rights
        if ($isOk === true && LoggedUser::isRoot()) {
            return true;
        }

        $primaryKey = array(
            'user_type_id' => LoggedUser::getInstance()->user_type_id,
            'controller' => $ctl,
        );
        $utcp = new UTCP();
        try {
            $utcp->loadBy($primaryKey);
        } catch (NotFoundException $e) {
            $isOk = false;
        }

        if ($isOk === true && ($actionType & UTCP::TYPE_SELECT) === UTCP::TYPE_SELECT) {
            $isOk &= ($utcp->permissions & UTCP::TYPE_SELECT) === UTCP::TYPE_SELECT;
        }

        if ($isOk === true && ($actionType & UTCP::TYPE_INSERT) === UTCP::TYPE_INSERT) {
            $isOk &= ($utcp->permissions & UTCP::TYPE_INSERT) === UTCP::TYPE_INSERT;
        }

        if ($isOk === true && ($actionType & UTCP::TYPE_UPDATE) === UTCP::TYPE_UPDATE) {
            $isOk &= ($utcp->permissions & UTCP::TYPE_UPDATE) === UTCP::TYPE_UPDATE;
        }

        if ($isOk === true && ($actionType & UTCP::TYPE_DELETE) === UTCP::TYPE_DELETE) {
            $isOk &= ($utcp->permissions & UTCP::TYPE_DELETE) === UTCP::TYPE_DELETE;
        }

        return $isOk;
    }
}
