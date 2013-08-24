<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Route;

class ListRowAction
{
    protected $link = null;
    protected $route = null;

    public function __construct(Link $link, Route $route)
    {
        $this->link = $link;
        $this->route = $route;
    }

    public function getLink($primaryKeyParams)
    {
        $this->route->addParams($primaryKeyParams);
        $this->link->addCustomAttribute('href', $this->route->getURL());

        return $this->link;
    }
}
