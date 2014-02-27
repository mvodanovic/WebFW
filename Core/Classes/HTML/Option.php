<?php

namespace mvodanovic\WebFW\Core\Classes\HTML;

use mvodanovic\WebFW\Core\Classes\HTML\Base\SimpleFormItem;

class Option extends SimpleFormItem
{
    public function __construct($selected = false, $value = null, $caption = null)
    {
        $this->setInnerHTML(htmlspecialchars($caption));

        if ($selected === true) {
            $this->setAttribute('selected', $selected);
        }

        parent::__construct(SimpleFormItem::TYPE_OPTION, null, $value);
    }
}
