<?php

namespace WebFW\Core;

use WebFW\Core\Exception;
use WebFW\Core\Exceptions\NotFoundException;

/**
 * Class Request
 *
 * Handles the request received from the client.
 * Is a singleton.
 *
 * @package WebFW\Core
 */
class Request
{
    /**
     * Request values reference.
     *
     * @var array
     * @internal
     */
    protected $values = array();

    /**
     * Instance of the Request class.
     *
     * @var Request
     * @internal
     */
    protected static $instance = null;

    /**
     * Constructor.
     *
     * @internal
     */
    protected function __construct()
    {
        $this->values = &$_REQUEST;
        foreach ($this->values as $key => $value) {
            if ($value === '') {
                unset ($this->values[$key]);
            }
        }

        $this->parseRequest();
    }

    /**
     * If the request is a 4xx or 5xx error redirect, handles it with an appropriate exception.
     *
     * @throws Exception The exception based on the incoming redirect type
     * @internal
     */
    public static function handleIncomingRedirection()
    {
        /// Don't try to match the request if it hasn't been redirected
        if (!array_key_exists('REDIRECT_STATUS', $_SERVER)) {
            return;
        }

        if ($_SERVER['REDIRECT_STATUS'] >= 400 && $_SERVER['REDIRECT_STATUS'] <= 599) {
            throw new Exception(null, $_SERVER['REDIRECT_STATUS']);
        }
    }

    /**
     * Returns a singleton instance of the Request class.
     *
     * @return Request
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Parses the request and extracts parameters from it based on defined patterns.
     *
     * @throws Exceptions\NotFoundException If the route cannot be matched to any known pattern
     * @internal
     */
    protected function parseRequest()
    {
        $requestURI = $_SERVER['REQUEST_URI'];
        $matchFound = false;
        $queryParamsStartPosition = strpos($requestURI, '?');
        if ($queryParamsStartPosition !== false) {
            $requestURI = substr($requestURI, 0, $queryParamsStartPosition);
        }

        /// Iterate through route definitions provided by the router
        foreach (Router::getInstance()->getRouteDefs() as $routeDef) {
            $pattern = Config::get('General', 'rewriteBase') . $routeDef['pattern'];
            $variables = &$routeDef['variables'];
            $parameters = array();

            /// Replace parameters in pattern with regex blocks & extract parameter names
            $pattern = preg_replace_callback('#:([a-zA-Z0-9]+):#', function($matches) use(&$variables, &$parameters) {
                $parameters[] = $matches[1];
                if (array_key_exists($matches[1], $variables)) {
                    $replacement = $variables[$matches[1]];
                } else {
                    $routerClassName = Router::getClass();
                    $replacement = $routerClassName::ROUTE_VARIABLE_REGEX;
                }
                return '(' . $replacement . ')';
            }, $pattern);

            /// Attempt to match the pattern against the request URI
            preg_match("#^$pattern$#", $requestURI, $matches);
            if (empty($matches)) {
                continue;
            }

            /// Assign values to parameters from the matching request URI
            foreach ($parameters as $i => $param) {
                $this->values[$param] = $matches[$i + 1];
            }

            /// Append corresponding route values to request values
            $route = &$routeDef['route'];
            if ($route->controller !== null) {
                $this->values['ctl'] = $route->controller;
            }
            if ($route->namespace !== null) {
                $this->values['ns'] = $route->namespace;
            }
            if ($route->action !== null) {
                $this->values['action'] = $route->action;
            }
            if (is_array($route->params)) {
                $this->values += $route->params;
            }

            /// When a match is found, abort further matching
            $matchFound = true;
            break;
        }

        if (!$matchFound && $requestURI !== Config::get('General', 'rewriteBase')) {
            throw new NotFoundException('No route defined for URI ' . $requestURI);
        }
    }

    /**
     * Checks if a request value with the given key exists.
     *
     * @param string $key The key to check the request for
     * @return bool True if the key exists, false otherwise
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * Gets the request value with the given key.
     *
     * @param string $key The key to get the value for
     * @return mixed|null The value, or null if the key doesn't exist
     */
    public function __get($key)
    {
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    /**
     * Gets the request value for the given key.
     *
     * @param string $key The key to set the value for
     * @param mixed|null $value The value to be set
     */
    public function __set($key, $value = null)
    {

        if (is_null($value)) {
            if (isset($this->values[$key])) {
                unset($this->values[$key]);
            }
        } else {
            $this->values[$key] = $value;
        }
    }

    /**
     * Unsets the value with the given key.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        if (array_key_exists($key, $this->values)) {
            unset($this->values[$key]);
        }
    }

    /**
     * Alias of __get().
     *
     * @param string $key
     * @return mixed|null
     * @see __get()
     * @deprecated
     */
    public function getValue($key)
    {
        return $this->__get($key);
    }

    /**
     * Alias of __set().
     *
     * @param string $key
     * @param mixed|null $value
     * @see __set()
     * @deprecated
     */
    public function setValue($key, $value = null)
    {
        $this->__set($key, $value);
    }

    /**
     * Get all the request values.
     *
     * @return array The list of key - value pairs of the request
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Get a list of request values with a common prefix.
     *
     * @param string $prefix The prefix of keys to get values for
     * @param bool $keepPrefix If set to true, the prefix will be kept, otherwise it will be stripped
     * @return array Key - value pairs which match the requested criteria
     */
    public function getValuesWithPrefix($prefix, $keepPrefix = true)
    {
        $prefixedArray = array();
        $prefixLength = strlen($prefix);

        foreach ($this->values as $key => $value) {
            /// startsWith()
            if (!strncmp($key, $prefix, $prefixLength)) {
                if ($keepPrefix !== true) {
                    $key = substr($key, $prefixLength);
                }
                $prefixedArray[$key] = $value;
            }
        }

        return $prefixedArray;
    }

    /**
     * Alias of __unset().
     *
     * @param $key
     * @see __unset()
     * @deprecated
     */
    public function removeValue($key)
    {
        $this->__unset($key);
    }
}
