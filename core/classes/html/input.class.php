<?php

namespace WebFW\Core\Classes\HTML;

use \WebFW\Core\Classes\HTML\Base\BaseFormItem;

class Input extends BaseFormItem
{
    protected $tagName = 'input';
    protected $hasClosingTag = false;

    public function __construct($name = null, $value = null, $type = null, $class = null, $id = null)
    {
        parent::__construct($name, $value, $id);

        if ($type !== null) {
            $this->addCustomAttribute('type', $type);
            if ($type === 'checkbox') {
                $this->addCustomAttribute('value', '1');
            }
        }

        if ($class !== null) {
            $this->classes[] = $class;
        }
    }

    public static function get($name = null, $value = null, $type = null, $class = null, $id = null)
    {
        $imageObject = new static($name, $value, $type, $class, $id);
        return $imageObject->parse();
    }

    public function prepareHTMLChunks()
    {
        if ($this->value !== null) {
            $this->addCustomAttribute('value', $this->value);
        }

        parent::prepareHTMLChunks();
    }
}
