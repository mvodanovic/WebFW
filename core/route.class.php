<?php

namespace WebFW\Core;

use WebFW\Core\Classes\BaseClass;

class Route extends BaseClass
{
    protected $controller;
    protected $action;
    protected $namespace;
    protected $params;

    public function __construct($controller, $action = null, $namespace = null, $params = array())
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->namespace = $namespace;
        $this->params = $params;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    public function getURL($escapeAmps = true, $rawurlencode = true)
    {
        return Router::getInstance()->URLFromRoute($this, $escapeAmps, $rawurlencode);
    }

    public function addParams(array $params = null)
    {
        if (is_array($params)) {
            $this->params = array_merge(is_array($this->params) ? $this->params : array(), $params);
        }
    }
}
