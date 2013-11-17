<?php

namespace WebFW\Core\Classes\HTML\Base;

use WebFW\Core\Classes\BaseClass;

abstract class BaseHTMLItem extends BaseClass
{
    protected $image;
    protected $value;
    protected $classes = array();
    protected $styles = array();
    protected $innerHTMLElements = array();
    protected $attributes = array();
    protected $events = array();
    protected $hasClosingTag = true;
    protected $tagName = null;
    protected $skipInnerHTMLDecoration = false;

    protected $innerHTML = '';
    protected $attributesHTML = '';

    public function __construct($value)
    {
        $this->setValue($value);
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function setValue($value)
    {
        $this->value = $value === null ? null : (string) $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function addClass($class)
    {
        $this->classes[] = $class;
    }

    public function addStyle($key, $value)
    {
        $this->styles[$key] = $value;
    }

    public function addEvent($eventName, $functionName, $functionParameters = null)
    {
        $this->events[] = array(
            'eventName' => $eventName,
            'functionName' => $functionName,
            'functionParameters' => $functionParameters,
        );
    }

    public function addCustomAttribute($key, $value)
    {
        $this->attributes[$key] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }

    public function prepareHTMLChunks()
    {
        $innerHTMLElements = array();
        $attributes = array();
        $styles = array();

        if ($this->skipInnerHTMLDecoration === true) {
            $innerHTMLElements[] = htmlspecialchars($this->value);
        } else {
            if ($this->image !== null) {
                $innerHTMLElements[] = '<span class="ui-icon ' . $this->image . '"></span>';
            }

            if ($this->value !== null) {
                $innerHTMLElements[] = '<span>' . htmlspecialchars($this->value) . '</span>';
            }
        }

        $this->innerHTML = implode('', $this->innerHTMLElements + $innerHTMLElements);

        if (!empty($this->events)) {
            $this->attributes['data-events'] = 'data-events="'
                . htmlspecialchars(json_encode($this->events)) . '"';
        }

        foreach ($this->styles as $key => $value) {
            $styles[$key] = $key . ': ' . $value . ';';
        }
        if (!empty($styles)) {
            $this->attributes['style'] = 'style="' . implode(' ', $styles) . '"';
        }

        if (!empty($this->classes)) {
            $this->attributes['class'] = 'class="' . implode(' ', $this->classes) . '"';
        }

        $this->attributesHTML = ' ' . implode(' ', $this->attributes + $attributes);
    }

    public function parse()
    {
        $this->prepareHTMLChunks();

        if ($this->hasClosingTag === true) {
            return '<' . $this->tagName
                . $this->attributesHTML
                . '>'
                . $this->innerHTML
                . '</' . $this->tagName . '>';
        } else {
            return '<' . $this->tagName
                . $this->attributesHTML
                . ' />';
        }
    }
}
