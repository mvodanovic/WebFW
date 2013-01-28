<?php

namespace WebFW\Menu;

class Menu extends BaseNode
{
    protected $name;

    public function __construct($name)
    {
        $this->name = (string) $name;
    }
}
