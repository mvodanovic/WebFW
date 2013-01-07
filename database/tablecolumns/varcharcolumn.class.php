<?php

namespace WebFW\Database\TableColumns;

class VarcharColumn extends Column
{
    public function __construct($name, $nullable = true, $precision)
    {
        parent::__construct($name, static::TYPE_VARCHAR, $nullable, $precision);
    }
}
