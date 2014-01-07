<?php

namespace WebFW\Framework\Core;

use WebFW\Framework\Core\Classes\BaseClass;

class SessionHandler extends BaseClass
{
    public static function get($key)
    {
        $key = static::getSessionKey($key);

        if (!array_key_exists($key, $_SESSION)) {
            return null;
        }

        return unserialize($_SESSION[$key]);
    }

    public static function set($key, $value)
    {
        $_SESSION[static::getSessionKey($key)] = serialize($value);
    }

    public static function kill($key)
    {
        unset($_SESSION[static::getSessionKey($key)]);
    }

    protected static function getSessionKey($key)
    {
        return Config::get('General', 'projectName') . '-' .  $key;
    }
}
