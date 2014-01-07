<?php

namespace WebFW\Framework\Database\TableColumns;

use WebFW\Framework\Database\Table;

class DoublePrecisionColumn extends Column
{
    public function __construct(Table $table, $name, $nullable = true)
    {
        parent::__construct($table, $name, static::TYPE_DOUBLE, $nullable);
    }
}
