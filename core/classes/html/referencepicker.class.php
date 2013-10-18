<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Core\Route;

class ReferencePicker extends BaseFormItem
{
    protected $tagName = 'div';
    protected $skipInnerHTMLDecoration = true;
    protected $route;
    protected $dataName;
    protected $dataValue;

    public function __construct(Route $route, $name = null, $value = null)
    {
        $this->route = $route;
        $this->dataName = $name;
        $this->dataValue = $value;

        parent::__construct(null, null);
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
        $this->addCustomAttribute('data-name', $this->dataName);
        $this->addCustomAttribute('data-value', $this->dataValue);
        $this->addCustomAttribute('data-caption', 'Test caption');
        $this->addClass('reference_picker');

        parent::prepareHTMLChunks();
    }
}
