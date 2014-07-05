<?php

namespace mvodanovic\WebFW\CLI;

use mvodanovic\WebFW\Core\Classes\BaseClass;

class Terminal extends BaseClass
{
    protected static $instance = null;

    protected $width = null;

    /**
     * @return Terminal
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        if ($this->width === null) {
            $this->width = (int) exec('tput cols', $output, $code);
            if ($code > 0) {
                $this->width = 80;
            }
            if ($this->width < 52) {
                $this->width = 52;
            }
        }

        return $this->width;
    }
}
