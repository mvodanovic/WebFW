<?php

namespace mvodanovic\WebFW\Core\Classes\HTML\Base;

abstract class CompoundFormItem extends BaseFormItem
{
    protected $values = array();

    public function __construct($type = 'div')
    {
        $this->useLabel = false;
        parent::__construct($type, true);
    }

    public function getNames()
    {
        return array_keys($this->getValues());
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValue($name, $value)
    {
        if (array_key_exists($name, $this->values)) {
            $this->values[$name] = $value;
        }
    }

    public function setValues(array $values)
    {
        foreach ($values as $name => $value) {
            $this->setValue($name, $value);
        }
    }
}
