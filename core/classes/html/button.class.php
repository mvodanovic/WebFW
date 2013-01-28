<?php

namespace WebFW\Core\Classes\HTML;

use \WebFW\Core\Classes\HTML\Base\BaseFormItem;

class Button extends BaseFormItem
{
    protected $tagName = 'button';

    public function __construct($name = null, $value = null, $image = null, $type = null, $class = null, $id = null)
    {
        parent::__construct($name, $value, $id);

        $this->image = $image;

        if ($type !== null) {
            $this->addCustomAttribute('type', $type);
        }

        if ($class !== null) {
            $this->classes[] = $class;
        }
    }

    public static function get($name = null, $value = null, $image = null, $type = null, $class = null, $id = null)
    {
        $imageObject = new static($name, $value, $image, $type, $class, $id);
        return $imageObject->parse();
    }
}
