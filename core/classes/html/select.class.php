<?php

namespace WebFW\Core\Classes\HTML;

use \WebFW\Core\Classes\HTML\Base\BaseFormItem;

class Select extends BaseFormItem
{
    protected $tagName = 'select';
    protected $options = null;

    public function __construct($name = null, $value = null, $options = array(), $class = null, $id = null)
    {
        parent::__construct($name, $value, $id);

        $this->options = &$options;

        if ($class !== null) {
            $this->classes[] = $class;
        }
    }

    public function prepareHTMLChunks()
    {
        $this->innerHTMLElements[] = $this->generateOptionsHTML();
        $this->value = null;
        parent::prepareHTMLChunks();
    }

    public static function get($name = null, $value = null, $class = null, $id = null)
    {
        $selectObject = new static($name, $value, $class, $id);
        return $selectObject->parse();
    }

    protected function generateOptionsHTML()
    {
        $optionsHTML = '';
        foreach ($this->options as $value => $caption) {
            $selected = false;
            if (is_array($this->value) && in_array($value, $this->value)) {
                $selected = true;
            } elseif ($value === $this->value) {
                $selected = true;
            }

            $optionObject = new Option($selected, $value, $caption);
            $optionsHTML .= $optionObject->parse();
        }

        return $optionsHTML;
    }
}
