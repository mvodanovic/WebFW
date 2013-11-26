<?php

namespace WebFW\Core\Classes\HTML\Base;

use WebFW\Core\Classes\BaseClass;

class GeneralHTMLItem extends BaseClass
{
    protected $classes = array();
    protected $attributes = array();
    protected $events = array();
    protected $hasClosingTag = true;
    protected $tagName = null;
    protected $styles = array();
    protected $innerHTML = '';

    public function __construct($tagName = 'div', $hasClosingTag = true)
    {
        $this->tagName = $tagName;
        $this->hasClosingTag = $hasClosingTag;
    }

    public function addClass($class)
    {
        $this->classes[$class] = htmlspecialchars($class);
    }

    public function setStyle($key, $value)
    {
        $this->styles[$key] = htmlspecialchars($key) . ':' . htmlspecialchars($value) . ';';
    }

    public function setEvent($eventName, $functionName, $functionParameters = null)
    {
        $this->events[$eventName] = array(
            'eventName' => $eventName,
            'functionName' => $functionName,
            'functionParameters' => $functionParameters,
        );
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        if ($value === null && array_key_exists($key, $this->attributes)) {
            unset($this->attributes[$key]);
        } elseif (is_string($value) || is_float($value) || is_int($value)) {
            $this->attributes[$key] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        } elseif (is_bool($value)) {
            $this->attributes[$key] = htmlspecialchars($key) . '="' . (int) $value . '"';
        } else {
            $this->attributes[$key] = htmlspecialchars($key) . '="' . htmlspecialchars(json_encode($value)) . '"';
        }
    }

    public function setInnerHTML($html)
    {
        $this->innerHTML = $html;
    }

    public function parse()
    {
        $attributesHTML = implode(' ', $this->attributes);

        if (!empty($this->classes)) {
            $attributesHTML .= ' class="' . implode(' ', $this->classes) . '"';
        }

        if (!empty($this->styles)) {
            $attributesHTML .= ' style="' . implode('', $this->styles) . '"';
        }

        if (!empty($this->events)) {
            $attributesHTML .= ' data-events="' . htmlspecialchars(json_encode($this->events)) . '"';
        }

        if ($attributesHTML !== '') {
            $attributesHTML = ' ' . $attributesHTML;
        }

        if ($this->hasClosingTag === true) {
            return '<' . $this->tagName . $attributesHTML . '>' . $this->innerHTML . '</' . $this->tagName . '>';
        } else {
            return '<' . $this->tagName . $attributesHTML . ' />';
        }
    }
}
