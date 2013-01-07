<?php

namespace WebFW\Database\TableColumns;

class DecimalColumn extends Column
{
    public function __construct($name, $nullable = true, $precision = null, $scale = null)
    {
        parent::__construct($name, static::TYPE_DECIMAL, $nullable, array($precision, $scale));
    }
}
