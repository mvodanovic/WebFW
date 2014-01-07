<?php

namespace WebFW\Framework\CMS\Classes;

use WebFW\Framework\Core\Classes\HTML\Button;

class ListMassAction
{
    protected $button = null;

    public function __construct(Button $button)
    {
        $this->button = $button;
    }

    public function getButton()
    {
        return $this->button;
    }
}
