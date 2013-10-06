<?php

namespace WebFW\Core;

use WebFW\Core\Exceptions\NotFoundException;
use Config\Specifics\Data;

class Request
{
    protected $values = array();
    protected static $instance;

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
     * @return Request
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

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
            $pattern = Data::GetItem('APP_REWRITE_BASE') . $routeDef['pattern'];
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

        if (!$matchFound && $requestURI !== Data::GetItem('APP_REWRITE_BASE')) {
            throw new NotFoundException('No route defined for URI ' . $requestURI);
        }
    }

    public function __isset($key)
    {
        return array_key_exists($key, $this->values);
    }

    public function __get($key)
    {
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

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

    public function getValue($name)
    {
        return $this->__get($name);
    }

    public function setValue($name, $value)
    {
        $this->__set($name, $value);
    }

    public function getValues()
    {
        return $this->values;
    }

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

    public function removeValue($key)
    {
        if (array_key_exists($key, $this->values)) {
            unset($this->values[$key]);
        }
    }
}
