<?php

namespace mvodanovic\WebFW\Externals;

use mvodanovic\WebFW\Core\Exception;

class PHPTemplate
{
    protected $vars = array();
    protected $file;

    public function __construct($file, $directory = '')
    {
        if ($directory !== '') {
            $directory .= DIRECTORY_SEPARATOR;
        }
        $this->file = $directory . $file;
        if (!is_readable($this->file)) {
            throw new Exception('Cannot read template: ' . $this->file);
        }
    }

    /**
    * Sets a template variable.
    */
    public function set($name, $value)
    {
        $this->vars[$name] = $value instanceof static ? $value->fetch() : $value;
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function fetch()
    {
        if (is_array($this->vars)) {
            extract($this->vars);
        }

        ob_start();
        include($this->file);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}
