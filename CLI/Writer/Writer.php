<?php

namespace mvodanovic\WebFW\CLI\Writer;

use mvodanovic\WebFW\Core\Classes\BaseClass;

class Writer extends BaseClass
{
    protected static $instance = null;

    /**
     * @var iString[]
     */
    protected $buffer = [];

    /**
     * @var Style
     */
    protected $style = null;

    /**
     * @return Writer
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function clear()
    {
        $this->buffer = [];
        return $this;
    }

    public function add($text)
    {
        if ($text instanceof iString) {
            $this->buffer[] = $text;
        } else {
            $this->buffer[] = new String($text);
        }
        return $this;
    }

    public function addLine($text)
    {
        if ($text instanceof iString) {
            $this->add($text);
            $this->add(PHP_EOL);
        } else {
            $this->add($text . PHP_EOL);
        }
        return $this;
    }

    public function write($handle = STDOUT)
    {
        while (($bufferItem = array_shift($this->buffer)) !== null) {
            if ($bufferItem instanceof String) {
                $style = null;
                if ($bufferItem->getStyle() !== null) {
                    $style = $this->style;
                }
                fwrite($handle, $bufferItem);
                if ($style !== null) {
                    $this->style = $style;
                    fwrite($handle, $this->style);
                }
            } elseif ($bufferItem instanceof Style) {
                fwrite($handle, $bufferItem);
                $this->style = $bufferItem;
            }
        }

        return $this;
    }

    protected function __construct()
    {
        $this->style = (new Style())->setReset();
    }

    protected function __clone() {}
}
