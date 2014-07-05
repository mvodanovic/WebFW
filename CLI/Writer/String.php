<?php

namespace mvodanovic\WebFW\CLI\Writer;

use mvodanovic\WebFW\Core\Classes\BaseClass;

class String extends BaseClass implements iString
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var Style|null
     */
    protected $style = null;

    public function __construct($text)
    {
        $this->text = (string) $text;
    }

    public function append($text)
    {
        $this->text .= $text;
        return $this;
    }

    public function prepend($text)
    {
        $this->text = $text . $this->text;
        return $this;
    }

    public function replaceWith($text)
    {
        $this->text = $text;
        return $this;
    }

    public function getLength()
    {
        return mb_strlen($this->text);
    }

    public function setStyle(Style $style)
    {
        $this->style = $style;
        return $this;
    }

    public function getStyle()
    {
        return $this->style;
    }

    public function __toString()
    {
        if ($this->style) {
            return $this->style . $this->text;
        } else {
            return $this->text;
        }
    }
}
