<?php

namespace WebFW\Database\TableColumns;

use WebFW\Database\Table;

class VarcharColumn extends Column
{
    public function __construct(Table $table, $name, $nullable = true, $precision = null)
    {
        parent::__construct($table, $name, static::TYPE_VARCHAR, $nullable, $precision);
    }
}
