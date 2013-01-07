<?php

namespace WebFW\Database\TableColumns;

class IntegerColumn extends Column
{
    public function __construct($name, $nullable = true, $precision = null)
    {
        parent::__construct($name, static::TYPE_INTEGER, $nullable, $precision);
    }
}
