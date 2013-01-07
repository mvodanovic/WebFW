<?php

namespace WebFW\Database\TableConstraints;

class PrimaryKey extends Constraint
{
    public function __construct($columns, $name = null)
    {
        parent::__construct(static::TYPE_PRIMARY_KEY, $columns, $name);
    }
}
