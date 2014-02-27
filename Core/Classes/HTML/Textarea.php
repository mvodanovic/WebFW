<?php

namespace mvodanovic\WebFW\Core\Classes\HTML;

use mvodanovic\WebFW\Core\Classes\HTML\Base\SimpleFormItem;

class Textarea extends SimpleFormItem
{
    public function __construct($name = null, $value = null)
    {
        $this->setInnerHTML($value);
        parent::__construct(SimpleFormItem::TYPE_TEXTAREA, $name);
    }
}
