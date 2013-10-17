<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Route;
use WebFW\Database\TableGateway;

class ListRowAction
{
    protected $link = null;
    protected $route = null;
    protected $handlerFunction = null;
    protected $setPrimaryKeyInDataAttribute;

    public function __construct(Link $link = null, Route $route = null, $setPrimaryKeyInDataAttribute = false)
    {
        $this->link = $link;
        $this->route = $route;
        $this->setPrimaryKeyInDataAttribute = $setPrimaryKeyInDataAttribute;
    }

    public function getLink($params = null)
    {
        if (!($this->link instanceof Link)) {
            return null;
        }

        if (is_array($params) && $this->route instanceof Route) {
            $prefixedParams = array();
            foreach ($params as $key => $value) {
                $prefixedParams[TableGateway::PRIMARY_KEY_PREFIX . $key] = $value;
            }
            $this->route->addParams($prefixedParams);
            $this->link->addCustomAttribute('href', $this->route->getURL(false));
        }

        if ($this->setPrimaryKeyInDataAttribute === true && is_array($params)) {
            $this->link->addCustomAttribute('data-primary-key', json_encode($params, JSON_FORCE_OBJECT));
        }

        return $this->link;
    }

    public function setHandlerFunction($handlerFunction)
    {
        $this->handlerFunction = $handlerFunction;
    }

    public function getHandlerFunction()
    {
        return $this->handlerFunction;
    }
}
