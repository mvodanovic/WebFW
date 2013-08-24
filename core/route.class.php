<?php

namespace WebFW\Core;

class Route
{
    protected $controller;
    protected $action;
    protected $namespace;
    protected $params;
    protected $escapeAmps;
    protected $rawurlencode;

    public function __construct($controller, $action = null, $namespace = null, $params = array(), $escapeAmps = true, $rawurlencode = true)
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->namespace = $namespace;
        $this->params = $params;
        $this->escapeAmps = $escapeAmps;
        $this->rawurlencode = $rawurlencode;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }

    public function getURL()
    {
        return Router::URLFromRoute($this);
    }

    public function addParams($params)
    {
        $this->params = array_merge($this->params, $params);
    }

    public function isRawurlencode()
    {
        return $this->rawurlencode;
    }
}
