<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\SimpleFormItem;

class Select extends SimpleFormItem
{
    protected $tagName = 'select';
    protected $options = null;
    protected $selectedOptionValue = null;

    public function __construct($name = null, $options = array(), $value = null)
    {
        $this->options = $options;

        parent::__construct(SimpleFormItem::TYPE_SELECT, $name, $value);
    }

    public function setValue($value)
    {
        $this->selectedOptionValue = $value;
    }

    public function parse()
    {
        $optionsHTML = '';
        foreach ($this->options as &$valueCaptionList) {
            $selected = false;
            if (is_array($this->selectedOptionValue) && in_array($valueCaptionList['key'], $this->selectedOptionValue)) {
                $selected = true;
            } elseif ($valueCaptionList['key'] === $this->selectedOptionValue) {
                $selected = true;
            }

            $optionObject = new Option($selected, $valueCaptionList['key'], $valueCaptionList['value']);
            $optionsHTML .= $optionObject->parse();
        }

        $this->setInnerHTML($optionsHTML);

        return parent::parse();
    }
}
