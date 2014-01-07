<?php

namespace WebFW\Framework\Core\Classes\HTML;

use WebFW\Framework\Core\Classes\HTML\Base\SimpleFormItem;

class Textarea extends SimpleFormItem
{
    public function __construct($name = null, $value = null)
    {
        $this->setInnerHTML($value);
        parent::__construct(SimpleFormItem::TYPE_TEXTAREA, $name);
    }
}
