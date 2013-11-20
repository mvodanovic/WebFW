<?php

namespace WebFW\Database\TableColumns;

use WebFW\Database\Table;

class DecimalColumn extends Column
{
    public function __construct(Table $table, $name, $nullable = true, $precision = null, $scale = null)
    {
        parent::__construct($table, $name, static::TYPE_DECIMAL, $nullable, array($precision, $scale));
    }
}
