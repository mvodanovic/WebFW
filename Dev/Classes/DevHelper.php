<?php

namespace WebFW\Dev\Classes;

use WebFW\Core\Classes\BaseClass;
use WebFW\Core\Config;
use WebFW\Core\Exceptions\ForbiddenException;
use WebFW\Core\Request;

/**
 * Class DevHelper
 *
 * General helper class for the Dev module.
 *
 * @package WebFW\Dev
 */
class DevHelper extends BaseClass
{
    /**
     * Checks if the current request is considered as a dev request.
     *
     * @return bool Flag indicating if it is a dev request or isn't
     */
    public static function isDevRequest()
    {
        return Request::getInstance()->exists('__dev');
    }

    /**
     * Request authentication from the user to proceed if applicable.
     * If the project is in dev mode, no authentication is required.
     * If it isn't in dev mode and there are no developer credentials specified, a ForbiddenException is thrown.
     *
     * @param string $realmMessage The realm message to display to the user when requesting authentication
     * @throws \WebFW\Core\Exceptions\ForbiddenException
     */
    public static function requestAuthentication($realmMessage)
    {
        if (Config::get('Developer', 'devModeEnabled') !== true) {
            if (Config::get('Developer', 'authUsername') === null || Config::get('Developer', 'authPassword') === null) {
                throw new ForbiddenException($realmMessage);
            }

            AuthenticationHelper::authenticate(
                $realmMessage,
                Config::get('Developer', 'authUsername'),
                Config::get('Developer', 'authPassword')
            );
        }
    }
}
