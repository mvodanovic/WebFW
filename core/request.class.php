<?php

namespace WebFW\Core;

class Request
{
    protected $values = array();
    protected static $instance;

    protected function __construct()
    {
        $this->values = &$_REQUEST;
        foreach ($this->values as $key => $value) {
            if ($value === '') {
                unset ($this->values[$key]);
            }
        }
    }

    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function __isset($key)
    {
        return array_key_exists($key, $this->values);
    }

    public function __get($key)
    {
        return isset($this->values[$key]) ? $this->values[$key] : null;
    }

    public function __set($key, $value = null)
    {

        if (is_null($value)) {
            if (isset($this->values[$key])) {
                unset($this->values[$key]);
            }
        } else {
            $this->values[$key] = $value;
        }
    }

    public function getValue($name)
    {
        return $this->__get($name);
    }

    public function setValue($name, $value)
    {
        $this->__set($name, $value);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function removeValue($key)
    {
        if (array_key_exists($key, $this->values)) {
            unset($this->values[$key]);
        }
    }
}
