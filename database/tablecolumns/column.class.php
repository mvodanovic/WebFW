<?php

namespace WebFW\Database\TableColumns;

class Column
{
    const TYPE_BOOLEAN = 111;
    const TYPE_INTEGER = 211;
    const TYPE_SMALLINT = 212;
    const TYPE_FLOAT = 221;
    const TYPE_REAL = 222;
    const TYPE_DOUBLE = 223;
    const TYPE_NUMERIC = 231;
    const TYPE_DECIMAL = 232;
    const TYPE_CHAR = 311;
    const TYPE_VARCHAR = 312;
    const TYPE_NCHAR = 313;
    const TYPE_NVARCHAR = 314;
    const TYPE_BIT = 321;
    const TYPE_VARBIT = 322;
    const DATE = 411;
    const TIME = 412;
    const DATETIME = 413;
    const TIME_TZ = 414;
    const DATETIME_TZ = 415;

    protected $name;
    protected $type;
    protected $precision;
    protected $nullable;
    protected $defaultValue = null;
    protected $defaultValueIsSet = false;

    public function __construct($name, $type, $nullable = true, $precision = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->precision = $precision;
        $this->nullable = $nullable;
    }

    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
        $this->defaultValueIsSet = true;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function isDefaultValueSet()
    {
        return $this->defaultValueIsSet;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isNullable()
    {
        return $this->nullable;
    }

    public function getPrecision()
    {
        return $this->precision;
    }
}
