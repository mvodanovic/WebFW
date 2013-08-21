<?php

namespace WebFW\Core\Classes\HTML;

use \WebFW\Core\Classes\HTML\Base\BaseFormItem;

class Textarea extends BaseFormItem
{
    protected $tagName = 'textarea';
    protected $skipInnerHTMLDecoration = true;

    public function __construct($name = null, $value = null, $class = null, $id = null)
    {
        parent::__construct($name, $value, $id);

        if ($class !== null) {
            $this->classes[] = $class;
        }
    }

    public static function get($name = null, $value = null, $class = null, $id = null)
    {
        $imageObject = new static($name, $value, $class, $id);
        return $imageObject->parse();
    }
}
