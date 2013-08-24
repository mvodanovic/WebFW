<?php

namespace WebFW\CMS\Classes;

use \WebFW\Core\Classes\HTML\Base\BaseHTMLItem;

class EditAction
{
    protected $HTMLItem = null;
    protected $isRightAligned = false;

    public function __construct(BaseHTMLItem $HTMLItem)
    {
        $this->HTMLItem = $HTMLItem;
    }

    public function makeRightAligned()
    {
        $this->isRightAligned = true;
    }

    public function getHTMLItem()
    {
        return $this->HTMLItem;
    }

    public function isRightAligned()
    {
        return $this->isRightAligned;
    }
}
