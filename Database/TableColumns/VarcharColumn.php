<?php

namespace mvodanovic\WebFW\Database\TableColumns;

use mvodanovic\WebFW\Database\Table;

class VarcharColumn extends Column
{
    public function __construct(Table $table, $name, $nullable = true, $precision = null)
    {
        parent::__construct($table, $name, static::TYPE_VARCHAR, $nullable, $precision);
    }
}
