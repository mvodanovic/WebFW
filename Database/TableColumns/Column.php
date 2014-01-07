<?php

namespace WebFW\Framework\Database\TableColumns;

use WebFW\Framework\Database\Table;

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

    protected $table;
    protected $name;
    protected $type;
    protected $precision;
    protected $nullable;
    protected $defaultValue = null;
    protected $defaultValueIsSet = false;
    protected $hasAutoIncrement = false;

    /**
     * @return static
     */
    public static function spawn()
    {
        $rc = new \ReflectionClass(get_called_class());
        return $rc->newInstanceArgs(func_get_args());
    }

    public function __construct(Table $table, $name, $type, $nullable = true, $precision = null)
    {
        $this->table = $table;
        $this->name = $name;
        $this->type = $type;
        $this->precision = $precision;
        $this->nullable = $nullable;
    }

    public function setDefaultValue($value, $hasAutoIncrement = false)
    {
        $this->defaultValue = $value;
        $this->hasAutoIncrement = (boolean) $hasAutoIncrement;
        $this->defaultValueIsSet = true;

        return $this;
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

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    public function isNullable()
    {
        return $this->nullable;
    }

    public function getPrecision()
    {
        return $this->precision;
    }

    public function hasAutoIncrement()
    {
        return $this->hasAutoIncrement;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
