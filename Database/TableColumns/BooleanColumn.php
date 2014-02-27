<?php

namespace mvodanovic\WebFW\Database\TableColumns;

use mvodanovic\WebFW\Database\Table;

class BooleanColumn extends Column
{
    public function __construct(Table $table, $name, $nullable = true)
    {
        parent::__construct($table, $name, static::TYPE_BOOLEAN, $nullable);
    }
}
