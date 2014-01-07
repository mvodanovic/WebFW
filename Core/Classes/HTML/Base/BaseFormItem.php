<?php

namespace WebFW\Core\Classes\HTML\Base;

abstract class BaseFormItem extends GeneralHTMLItem
{
    protected $useLabel = null;
    protected $namePrefix = null;
    protected $isAutocompleteDisabled = false;

    public function __construct($type, $hasClosingTag)
    {
        parent::__construct($type, $hasClosingTag);
    }

    public function useLabel()
    {
        return $this->useLabel;
    }

    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;
    }

    public function disableAutocomplete()
    {
        $this->isAutocompleteDisabled = true;
    }
}
