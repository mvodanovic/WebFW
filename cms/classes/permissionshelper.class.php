<?php

namespace WebFW\CMS\Classes;

use WebFW\CMS\Controller;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;

class PermissionsHelper
{
    public static function checkForController(Controller $controller, $actionType)
    {
        $isOk = LoggedUser::isLoggedIn();

        if ($isOk === true && ($actionType & UTCP::TYPE_SELECT) === UTCP::TYPE_SELECT) {
            /// TODO: select check
            $isOk &= true;
        }

        if ($isOk === true && ($actionType & UTCP::TYPE_INSERT) === UTCP::TYPE_INSERT) {
            /// TODO: insert check
            $isOk &= true;
        }

        if ($isOk === true && ($actionType & UTCP::TYPE_UPDATE) === UTCP::TYPE_UPDATE) {
            /// TODO: update check
            $isOk &= true;
        }

        if ($isOk === true && ($actionType & UTCP::TYPE_DELETE) === UTCP::TYPE_DELETE) {
            /// TODO: delete check
            $isOk &= true;
        }

        return $isOk;
    }
}
