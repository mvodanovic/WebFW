<?php

namespace WebFW\Database\TableColumns;

use WebFW\Database\Table;

class DoublePrecisionColumn extends Column
{
    public function __construct(Table $table, $name, $nullable = true)
    {
        parent::__construct($table, $name, static::TYPE_DOUBLE, $nullable);
    }
}
