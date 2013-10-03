<?php

namespace WebFW\Core;

use Config\Specifics\Data;
use WebFW\Core\Controller;

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

    public static function URL($controller, $action = null, $namespace = null, $params = array(), $escapeAmps = true, $rawurlencode = true)
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
            $urlParams = array('ctl=' . $encodeFunction($controller));

            if ($action !== Controller::DEFAULT_ACTION_NAME && $action !== null) {
                $urlParams[] = 'action=' . $encodeFunction($action);
            }

            if ($namespace !== null) {
                $urlParams[] = 'ns=' . $encodeFunction($namespace);
            }

            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    if ($key === '' || $value === '') {
                        continue;
                    }

                    $urlParams[] = $encodeFunction($key) . '=' . $encodeFunction($value);
                }
            }

            $url = Data::GetItem('APP_REWRITE_BASE') . '?' . implode($amp, $urlParams);
        }

        return $url;
    }

    public static function URLFromRoute(Route $route, $escapeAmps = true, $rawurlencode = true)
    {
        return static::URL(
            $route->controller,
            $route->action,
            $route->namespace,
            $route->params,
            $escapeAmps,
            $rawurlencode
        );
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
