<?php

namespace WebFW\Database\TableConstraints;

class Unique extends Constraint
{
    public function __construct($columns, $name = null)
    {
        parent::__construct(static::TYPE_UNIQUE, $columns, $name);
    }
}
