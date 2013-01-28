<?php

namespace WebFW\Menu;

use \Config\Specifics\Data;
use \WebFW\Core\Route;

class Item extends BaseNode
{
    protected $caption;
    protected $href = null;
    protected $hrefIsFull = false;
    protected $route = null;
    protected $active = false;

    public function __construct($caption)
    {
        $this->caption = $caption;
    }

    public function __get($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->$attribute;
        }

        return null;
    }

    public function setHref($href)
    {
        $this->href = (string) $href;
        $this->route = null;
    }

    public function setHrefIsFull($hrefIsFull)
    {
        $this->hrefIsFull = (bool) $hrefIsFull;
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;
        $this->href = null;
    }

    public function setActive($active)
    {
        $this->active = (bool) $active;
    }

    public function getURL()
    {
        if ($this->href !== null) {
            if ($this->hrefIsFull === false) {
                return Data::GetItem('APP_REWRITE_BASE') . $this->href;
            } else {
                return $this->href;
            }
        } elseif ($this->route !== null) {
            return $this->route->getURL();
        }

        return null;
    }
}
