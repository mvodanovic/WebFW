<?php

namespace WebFW\Framework\Core\Classes\HTML\Base;

class SimpleFormItem extends BaseFormItem
{
    const TYPE_INPUT = 'input';
    const TYPE_BUTTON = 'button';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT = 'select';
    const TYPE_OPTION = 'option';

    protected $shortenedTypes = array(
        self::TYPE_INPUT,
    );

    protected $name = null;
    protected $value = null;

    public function __construct($type, $name = null, $value = null)
    {
        $hasClosingTag = true;
        if (in_array($type, $this->shortenedTypes)) {
            $hasClosingTag = false;
        }

        $this->name = $name;
        $this->setValue($value);
        $this->useLabel = true;

        parent::__construct($type, $hasClosingTag);
    }

    public function disable()
    {
        $this->setAttribute('disabled', 'disabled');
    }

    protected function setReadOnly()
    {
        $this->setAttribute('readonly', 'readonly');
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function parse()
    {
        if ($this->name !== null && $this->name !== '') {
            $this->setAttribute('name', $this->namePrefix . $this->name);
        }
        $this->setAttribute('value', $this->value);
        if ($this->isAutocompleteDisabled === true) {
            $this->setAttribute('autocomplete', 'off');
        }

        return parent::parse();
    }
}
