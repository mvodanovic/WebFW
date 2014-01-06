<?php

namespace WebFW\Core;

use ReflectionClass;
use WebFW\CMS\CMSLogin;
use WebFW\CMS\Controllers\User;
use WebFW\Core\Classes\BaseClass;
use WebFW\Dev\Profiler;
use WebFW\Media\Controllers\CMS\Image;

/**
 * Class Router
 *
 * Handles route-to-URL conversions.
 *
 * @package WebFW\Core
 */
class Router extends BaseClass
{
    /** @var Router */
    protected static $instance;
    protected static $class = null;
    protected $routeDefinitions = array();

    const ROUTE_VARIABLE_REGEX = '[a-zA-Z0-9]+';

    /**
     * Singleton instance getter.
     *
     * @return Router
     */
    public static function getInstance()
    {
        if (static::$class === null) {
            static::$class = static::className();
        }

        if (!isset(static::$instance)) {
            static::$instance = new static::$class();
            Profiler::getInstance()->addMoment('After router construction');
        }

        return static::$instance;
    }

    /**
     * Class constructor.
     */
    protected function __construct() {
        $this->routeDefinitions[] = array(
            'pattern' => 'cms',
            'route' => new Route(CMSLogin::className()),
            'variables' => array(
            ),
        );

        $this->routeDefinitions[] = array(
            'pattern' => 'cms/webfw/:ctl:/:action:',
            'route' => new Route(null, null, User::classNamespace()),
            'variables' => array(
            ),
        );

        $this->routeDefinitions[] = array(
            'pattern' => 'cms/webfw/media/:ctl:/:action:',
            'route' => new Route(null, null, Image::classNamespace()),
            'variables' => array(
            ),
        );
    }

    public function getRouteDefinitions()
    {
        return $this->routeDefinitions;
    }

    /**
     * /// TODO: in progress
     * @param array $routeDefinition /// TODO: make it a table gateway
     * @param string $controller
     * @param null $action
     * @param array $params
     * @param string $amp
     * @param string $encodeFunction
     * @return string|null
     */
    protected function getURLForRouteDefinition(array $routeDefinition, $controller, $action = null,
        array $params = null, $amp = '&', $encodeFunction = 'rawurlencode')
    {
        $pattern = Config::get('General', 'rewriteBase') . $routeDefinition['pattern'];
        $route = $routeDefinition['route'];
        $variables = &$routeDefinition['variables'];
        if ($params === null) {
            $params = array();
        }

        if ($route->namespace !== null) {
            if (strpos($controller, $route->namespace) !== 0) {
                return null;
            }

            $controller = substr($controller, strlen($route->namespace) + 1);
        }

        /// Check parameters with route
        if ($route->controller !== null) {
            if ($controller !== $route->controller) {
                return null;
            }
            $controller = null;
        }
        if ($route->action !== null) {
            if ($action !== $route->action) {
                return null;
            }
            $action = null;
        }
        foreach ($route->params as $key => $value) {
            if (!array_key_exists($key, $params) || $params[$key] !== $value) {
                return null;
            }
            unset($params[$key]);
        }

        /// Get all parameter names from the URI pattern
        if (!(preg_match_all('#:([a-zA-Z0-9]+):#', $pattern, $matches) > 0)) {
            return null;
        }
        $matches = $matches[1];

        /// For each URI parameter...
        foreach ($matches as &$param) {
            /// Get the parameter's required regex pattern and check it
            $paramPattern = array_key_exists($param, $variables) ? $variables[$param] : static::ROUTE_VARIABLE_REGEX;
            switch ($param) {
                case 'ctl':
                    if (!preg_match("#^$paramPattern$#", $controller)) {
                        return null;
                    }
                    break;
                case 'action':
                    if (!preg_match("#^$paramPattern$#", $action)) {
                        return null;
                    }
                    break;
                default:
                    if (!array_key_exists($param, $params)) {
                        return null;
                    }
                    if (!preg_match("#^$paramPattern$#", $params[$param])) {
                        return null;
                    }
                    break;
            }
        }

        /// Replace parameter placeholders in the URI pattern with actual values
        /// Those parameters which were injected are nullified
        $url = preg_replace_callback(
            "#:([a-zA-Z0-9]+):#",
            function($matches) use (&$controller, &$action, &$params, $encodeFunction) {
                switch ($matches[1]) {
                    case 'ctl':
                        $value = $encodeFunction($controller);
                        $controller = null;
                        break;
                    case 'action':
                        $value = $encodeFunction($action);
                        $action = null;
                        break;
                    default:
                        $value = $encodeFunction($params[$matches[1]]);
                        unset($params[$matches[1]]);
                        break;
                }

                return $value;
            },
            $pattern
        );

        /// For the remaining parameters which weren't injected, append the in the query string
        $urlParams = array();
        if ($controller !== null && $controller !== Config::get('General', 'defaultController')) {
            $urlParams[] = 'ctl=' . $encodeFunction($controller);
        }
        if ($action !== null && $action !== $controller::DEFAULT_ACTION_NAME) {
            $urlParams[] = 'action=' . $encodeFunction($action);
        }
        foreach ($params as $key => $value) {
            if ($key === '' || $value === '') {
                continue;
            }

            $urlParams[] = $encodeFunction($key) . '=' . $encodeFunction($value);
        }
        if (!empty($urlParams)) {
            $url .= '?' . implode($amp, $urlParams);
        }

        return $url;
    }

    /**
     * Get the URL for the given controller.
     *
     * @param string $controller The controller to get the URL for
     * @param string $action Action of the controller, NULL for default
     * @param array $params Additional parameters for the URL, key-value pairs
     * @param bool $escapeAmps Should amps '&' be escaped in the URL or not
     * @param bool $rawurlencode Should rawurlencode() be used (true) or urlencode() (false)
     * @return string|null The URL of the controller
     */
    public function URL($controller, $action = null, $params = array(),
        $escapeAmps = true, $rawurlencode = true)
    {
        /// Set the query param delimiter
        $amp = '&amp;';
        if ($escapeAmps !== true) {
            $amp = '&';
        }

        /// Set the function which will e used for escaping URI parameters
        $encodeFunction = 'rawurlencode';
        if ($rawurlencode !== true) {
            $encodeFunction = 'urlencode';
        }

        /// Setup empty parameters to their default values
        if ($controller === null) {
            $controller = Config::get('General', 'defaultController');
        }
        if (!class_exists($controller)) {
        /** @var Controller $controllerClass */
            /// TODO: Throw exception?
            return null;
        }
        if ($action === null) {
            $action = $controller::DEFAULT_ACTION_NAME;
        }

        /// Try to match the parameters with existing route definitions
        foreach ($this->routeDefinitions as &$routeDefinition) {
            $url = $this->getURLForRouteDefinition($routeDefinition, $controller, $action, $params, $amp, $encodeFunction);
            if ($url !== null) {
                return $url;
            }
        }

        /// Fallback, build the URL by appending parameters in the query string
        $urlParams = array();
        if ($controller !== Config::get('General', 'defaultController')) {
            $urlParams[] = 'ctl=' . $encodeFunction($controller);
        }
        if ($action !== $controller::DEFAULT_ACTION_NAME) {
            $urlParams[] = 'action=' . $encodeFunction($action);
        }
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                if ($key === '' || $value === '') {
                    continue;
                }

                $urlParams[] = $encodeFunction($key) . '=' . $encodeFunction($value);
            }
        }

        $url = Config::get('General', 'rewriteBase');
        if (!empty($urlParams)) {
            $url .= '?' . implode($amp, $urlParams);
        }

        return $url;
    }

    /**
     * Gets the URL for a given route.
     *
     * @param Route $route The route for which to get the URL
     * @param bool $escapeAmps Should amps '&' be escaped in the URL or not
     * @param bool $rawurlencode Should rawurlencode() be used (true) or urlencode() (false)
     * @return string The URL of the route
     */
    public function URLFromRoute(Route $route, $escapeAmps = true, $rawurlencode = true)
    {
        $controller = $route->controller;
        if ($route->namespace !== null) {
            $controller = $route->namespace . '\\' . $controller;
        }
        return $this->URL(
            $controller, $route->action, $route->params, $escapeAmps, $rawurlencode
        );
    }

    /**
     * Gets the current Router class name.
     *
     * @return string The currently set Router
     */
    public static function getClass()
    {
        return static::$class;
    }

    /**
     * Set a custom router class.
     *
     * @param string $className Full class name of the router to use, must extend the Router class
     * @param bool $forceNewInstance If set to true, the existing instance will be deleted
     * @throws Exception If a valid Router class name was not given
     */
    public static function setClass($className, $forceNewInstance = false)
    {
        if (!class_exists($className)) {
            throw new Exception('Class ' . $className . " doesn't exist");
        }

        $rc = new ReflectionClass($className);
        if (!($rc->newInstanceWithoutConstructor() instanceof Router)) {
            throw new Exception('Class ' . $className . ' is not an instance of WebFW\\Core\\Router');
        }

        static::$class = $className;

        if ($forceNewInstance === true) {
            static::$instance = null;
        }
    }

    /**
     * The router cannot be cloned.
     */
    final private function __clone() {}
}
