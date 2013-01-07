<?php

namespace WebFW\Core;

use \Config\Specifics\Data;

class Router
{
    protected static $_instance;
    protected static $_class;

    public static function GetInstance()
   {
        if (!isset(static::$_instance)) {
            static::$_class = get_called_class();
            static::$_instance = new static::$_class;
        }

        return self::$_instance;
   }

    public static function URL($controller, $action = 'execute', $params = array(), $escapeAmps = true, $rawurlencode = true)
    {
        if (!isset(static::$_instance)) {
            static::$_class = get_called_class();
            static::$_instance = new static::$_class;
        }

        $amp = '&amp;';
        if ($escapeAmps !== true) {
            $amp = '&';
        }

        $encodeFunction = 'rawurlencode';
        if ($rawurlencode !== true) {
            $encodeFunction = 'urlencode';
        }

        $url = '';

        if (Data::GetItem('APP_REWRITE_ACTIVE') === true) {
        } elseif (
            $controller === Data::GetItem('DEFAULT_CTL')
            && $action === Data::GetItem('DEFAULT_CTL_ACTION')
        ) {
            $url = Data::GetItem('APP_REWRITE_BASE');
        } else {
            $url = Data::GetItem('APP_REWRITE_BASE') . '?ctl=' . $encodeFunction($controller);
            if ($action !== 'Execute' && $action !== null) {
                $url .= $amp . 'action=' . $encodeFunction($action);
            }

            if (is_array($params)) {
                foreach ($params as $key => $value)
                {
                    $key = trim($key);
                    $value = trim($value);
                    if ($key === '' || $value === '') {
                        continue;
                    }

                    $url .= $amp . $encodeFunction($key) . '=' . $encodeFunction($value);
                }
            }
        }

        return $url;
    }

    public static function GetClass()
    {
        return static::$_class;
    }

    final public function __clone()
    {
        throw new Exception('Router is not cloneable.');
    }

    private function __construct() {}
}
