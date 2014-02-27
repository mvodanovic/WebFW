<?php

namespace mvodanovic\WebFW\CMS\Classes;

use mvodanovic\WebFW\Core\Classes\HTML\Button;

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
