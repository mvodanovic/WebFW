<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseFormItem;

class Input extends BaseFormItem
{
    protected $tagName = 'input';
    protected $hasClosingTag = false;
    protected $type = null;

    public function __construct($name = null, $type = null, $value = null, $class = null)
    {
        $this->setType($type);

        parent::__construct($name, $value);

        if ($class !== null) {
            $this->classes[] = $class;
        }
    }

    public function prepareHTMLChunks()
    {
        if ($this->value !== null) {
            if ($this->value === true) {
                $this->setValue('1');
            } elseif ($this->value === false) {
                $this->setValue('0');
            }
            if ($this->type !== 'checkbox') {
                $this->addCustomAttribute('value', $this->value);
            }
        }

        parent::prepareHTMLChunks();
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->addCustomAttribute('type', $type);
        if ($type === 'checkbox') {
            $this->addCustomAttribute('value', '1');
        }
    }

    public function setChecked()
    {
        if ($this->type === 'checkbox' || $this->type === 'radio') {
            $this->addCustomAttribute('checked', 'checked');
        }
    }

    public function getType()
    {
        return $this->type;
    }
}
