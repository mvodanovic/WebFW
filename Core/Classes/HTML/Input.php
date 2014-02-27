<?php

namespace mvodanovic\WebFW\Core\Classes\HTML;

use mvodanovic\WebFW\Core\Classes\HTML\Base\SimpleFormItem;

class Input extends SimpleFormItem
{
    const INPUT_TEXT = 'text';
    const INPUT_PASSWORD = 'password';
    const INPUT_EMAIL = 'email';
    const INPUT_RADIO = 'radio';
    const INPUT_CHECKBOX = 'checkbox';
    const INPUT_HIDDEN = 'hidden';
    const INPUT_FILE = 'file';
    const INPUT_TEL = 'tel';

    protected $tagName = 'input';
    protected $hasClosingTag = false;
    protected $type = null;

    public function __construct($name = null, $type = null, $value = null)
    {
        $this->setType($type);

        parent::__construct(static::TYPE_INPUT, $name, $value);
    }

    public function setValue($value)
    {
        if ($this->type !== static::INPUT_CHECKBOX) {
            parent::setValue($value);
        }
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->setAttribute('type', $type);
        if ($type === static::INPUT_CHECKBOX) {
            parent::setValue('1');
        }
    }

    public function setChecked()
    {
        if ($this->type === static::INPUT_CHECKBOX || $this->type === static::INPUT_RADIO) {
            $this->setAttribute('checked', 'checked');
        }
    }

    public function getType()
    {
        return $this->type;
    }
}
