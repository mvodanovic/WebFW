<?php

namespace WebFW\Core;

/**
 * Class Route
 *
 * Represents a single route to a controller which can be converted to a URL.
 *
 * @property $controller
 * @property $action
 * @property $namespace
 * @property $params
 * @package WebFW\Core
 */
class Route
{
    protected $controller;
    protected $action;
    protected $namespace;
    protected $params;

    /**
     * Class constructor.
     *
     * @param string $controller Route controller
     * @param string $action Route action, NULL for default
     * @param string $namespace Route namespace, NULL for default
     * @param array $params Additional route parameters, key-value list
     */
    public function __construct($controller, $action = null, $namespace = null, $params = array())
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->namespace = $namespace;
        $this->params = $params;
    }

    /**
     * Property getter.
     * If the property does not exist, NULL will be returned.
     *
     * @param string $property Property to get
     * @return mixed|null The value of the property
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }

    /**
     * Property setter.
     * Only existing class properties will be set.
     *
     * @param string $property Property to set
     * @param mixed $value Value to set the property to
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /**
     * Gets the URL from the route.
     *
     * @param bool $escapeAmps Should amps '&' be escaped in the URL or not
     * @param bool $rawurlencode Should rawurlencode() be used (true) or urlencode() (false)
     * @return string The URL of the route
     */
    public function getURL($escapeAmps = true, $rawurlencode = true)
    {
        return Router::getInstance()->URLFromRoute($this, $escapeAmps, $rawurlencode);
    }

    /**
     * Adds new parameters to the route.
     * If a key already exists, it will be overwritten.
     *
     * @param array $params List of key-value pairs to add to the route
     */
    public function addParams(array $params = null)
    {
        if (is_array($params)) {
            $this->params = array_merge(is_array($this->params) ? $this->params : array(), $params);
        }
    }
}
