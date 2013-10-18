<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseFormItem;

class Option extends BaseFormItem
{
    protected $tagName = 'option';
    protected $skipInnerHTMLDecoration = true;

    public function __construct($selected = false, $value = null, $caption = null, $class = null)
    {
        parent::__construct(null, $caption);

        if ($class !== null) {
            $this->classes[] = $class;
        }

        if ($selected === true) {
            $this->addCustomAttribute('selected', $selected);
        }

        if ($value !== null) {
            $this->addCustomAttribute('value', $value);
        }
    }
}
