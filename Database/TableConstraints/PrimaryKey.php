<?php

namespace WebFW\Framework\Database\TableConstraints;

use WebFW\Framework\Database\Table;

class PrimaryKey extends Constraint
{
    public function __construct(Table $table, $name = null)
    {
        parent::__construct($table, static::TYPE_PRIMARY_KEY, $name);
    }
}
