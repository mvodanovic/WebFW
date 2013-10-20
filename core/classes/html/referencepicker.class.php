<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Core\Route;

class ReferencePicker extends BaseFormItem
{
    protected $tagName = 'div';
    protected $useLabel = false;
    protected $skipInnerHTMLDecoration = true;
    protected $route;
    protected $dataName;
    protected $dataValue;

    public function __construct(Route $route, $name = null, $value = null)
    {
        $this->route = $route;
        $this->dataName = $name;
        $this->dataValue = $value;

        parent::__construct();
    }

    public function getName()
    {
        return $this->dataName;
    }

    public function setName($name)
    {
        $this->dataName = $name;
    }

    public function getValue()
    {
        return $this->dataValue;
    }

    public function setValue($value)
    {
        $this->dataValue = $value;
    }

    public function prepareHTMLChunks()
    {
        $this->route->addParams(array('popup' => 1));
        $this->addCustomAttribute('data-url', $this->route->getURL(false));
        $this->addCustomAttribute('data-field-name', $this->dataName);
        $this->addCustomAttribute('data-values', json_encode($this->dataValue, JSON_FORCE_OBJECT));
        $this->addCustomAttribute('data-caption', null);
        $this->addClass('reference_picker');

        parent::prepareHTMLChunks();
    }
}
