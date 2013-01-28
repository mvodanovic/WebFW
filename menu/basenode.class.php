<?php

namespace WebFW\Menu;

abstract class BaseNode
{
    protected $children = array();

    public function getChildren()
    {
        return $this->children;
    }

    public function addChild($id, Item $child)
    {
        $this->children[$id] = $child;
    }

    public function removeChild($id)
    {
        if (is_array($this->children) && array_key_exists($id, $this->children)) {
            unset($this->children[$id]);
        }
    }

    public function emptyChildren()
    {
        $this->children = array();
    }

    public function getChildrenCount()
    {
        return count($this->children);
    }
}
