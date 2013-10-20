<?php

namespace WebFW\Core\Classes\HTML\Base;

abstract class BaseFormItem extends BaseHTMLItem
{
    protected $name = null;
    protected $useLabel = true;
    protected $nameHTML = '';
    protected $valueHTML = '';

    public function __construct($name = null, $value = null)
    {
        parent::__construct($value);

        if ($name !== null) {
            $this->addCustomAttribute('name', $name);
            $this->name = $name;
        }
    }

    public function disable()
    {
        $this->addCustomAttribute('disabled', 'disabled');
    }

    protected function setReadOnly()
    {
        $this->addCustomAttribute('readonly', 'readonly');
    }

    public function setName($name)
    {
        $this->addCustomAttribute('name', $name);
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function useLabel()
    {
        return $this->useLabel;
    }
}
