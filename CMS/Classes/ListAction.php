<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Classes\HTML\Base\GeneralHTMLItem;

class ListAction
{
    protected $HTMLItem = null;
    protected $permissions = null;

    public function __construct(GeneralHTMLItem $HTMLItem)
    {
        $this->HTMLItem = $HTMLItem;
    }

    public function getHTMLItem()
    {
        return $this->HTMLItem;
    }

    public function setForPermissions($permissions)
    {
        $this->permissions = $permissions;
    }
}
