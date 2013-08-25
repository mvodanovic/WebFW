<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Classes\HTML\Base\BaseHTMLItem;

class ListAction
{
    protected $HTMLItem = null;

    public function __construct(BaseHTMLItem $HTMLItem)
    {
        $this->HTMLItem = $HTMLItem;
    }

    public function getHTMLItem()
    {
        return $this->HTMLItem;
    }
}
