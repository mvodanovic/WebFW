<?php

namespace WebFW\Core;

use \Config\Specifics\Data;

class Router
{
    protected static $instance;
    protected static $class;

    public static function GetInstance()
   {
        if (!isset(static::$instance)) {
            static::$class = get_called_class();
            static::$instance = new static::$class;
        }

        return static::$instance;
   }

    public static function URL($controller, $action = null, $params = array(), $escapeAmps = true, $rawurlencode = true)
    {
        if (!isset(static::$instance)) {
            static::GetInstance();
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

        if ($action === null) {
            $action = Controller::getDefaultActionName();
        }

        if (Data::GetItem('APP_REWRITE_ACTIVE') === true) {
        } elseif (
            $controller === Data::GetItem('DEFAULT_CTL')
            && $action === Controller::getDefaultActionName()
        ) {
            $url = Data::GetItem('APP_REWRITE_BASE');
        } else {
            $url = Data::GetItem('APP_REWRITE_BASE') . '?ctl=' . $encodeFunction($controller);
            if ($action !== \WebFW\Core\Controller::DEFAULT_ACTION_NAME && $action !== null) {
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
        return static::$class;
    }

    final public function __clone()
    {
        throw new Exception('Router is not cloneable.');
    }

    private function __construct() {}
}
