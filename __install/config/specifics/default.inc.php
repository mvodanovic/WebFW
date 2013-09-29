<?php

namespace Config\Specifics;

final class Data
{
    private static $_data = array(
        'APP_REWRITE_BASE' => '/',
        'SHOW_DEBUG_INFO' => true,
        'EXCEPTIONS_USE_HTML_OUTPUT' => true,
        'DISPLAY_ERRORS' => true,
        'ERROR_REPORTING' => -1,
        'DEFAULT_CTL' => null,
        'DB_USERNAME' => 'todo',
        'DB_PASSWORD' => 'todopwd',
        'DB_NAME' => 'todo',
        'DB_HOST' => '127.0.0.1',
        'DB_HANDLER' => '\WebFW\Database\PgSQLHandler',
    );

    public static function GetItem($key)
    {
        if (array_key_exists($key, self::$_data)) {
            return self::$_data[$key];
        } else {
            return null;
        }
    }

    private function __construct() {}
}
