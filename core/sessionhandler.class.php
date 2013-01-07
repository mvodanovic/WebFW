<?php

namespace WebFW\Core;

class SessionHandler
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
        return \WebFW\Config\APP_ID . '-' .  $key;
    }
}
