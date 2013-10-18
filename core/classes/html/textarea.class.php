<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseFormItem;

class Textarea extends BaseFormItem
{
    protected $tagName = 'textarea';
    protected $skipInnerHTMLDecoration = true;

    public function __construct($name = null, $value = null, $class = null)
    {
        parent::__construct($name, $value);

        if ($class !== null) {
            $this->classes[] = $class;
        }
    }
}
