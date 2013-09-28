<?php

namespace WebFW\CMS\Classes;

use WebFW\CMS\Controller;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use WebFW\Core\Exception;

class PermissionsHelper
{
    public static function checkForController(Controller $controller, $actionType)
    {
        return static::checkForControllerByName($controller->getName(), $controller->getNamespace(), $actionType);
    }

    public static function checkForControllerByName($ctl, $ns, $actionType)
    {
        $isOk = LoggedUser::isLoggedIn();

        /// Root users have all access rights
        if ($isOk === true && LoggedUser::isRoot()) {
            return true;
        }

        $primaryKey = array(
            'user_type_id' => LoggedUser::getInstance()->user_type_id,
            'controller' => $ctl,
            'namespace' => $ns,
        );
        $utcp = new UTCP();
        try {
            $utcp->loadBy($primaryKey);
        } catch (Exception $e) {
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
