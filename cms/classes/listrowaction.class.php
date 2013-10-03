<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Route;

class ListRowAction
{
    protected $link = null;
    protected $route = null;
    protected $handlerFunction = null;

    public function __construct(Link $link, Route $route)
    {
        $this->link = $link;
        $this->route = $route;
    }

    public function getLink($params = null)
    {
        if (is_array($params)) {
            $this->route->addParams($params);
            $this->link->addCustomAttribute('href', $this->route->getURL(false));
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
