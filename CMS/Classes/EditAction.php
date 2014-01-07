<?php

namespace WebFW\Framework\CMS\Classes;

use WebFW\Framework\Core\Classes\HTML\Base\GeneralHTMLItem;

class EditAction
{
    protected $HTMLItem = null;
    protected $isRightAligned = false;

    public function __construct(GeneralHTMLItem $HTMLItem)
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
