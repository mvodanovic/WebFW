<?php

namespace WebFW\Database\TableColumns;

class BooleanColumn extends Column
{
    public function __construct($name, $nullable = true)
    {
        parent::__construct($name, static::TYPE_BOOLEAN, $nullable);
    }
}
