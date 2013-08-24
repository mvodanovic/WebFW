<?php

namespace WebFW\Database\TableConstraints;

use WebFW\Core\Exception;

abstract class Constraint
{
    const TYPE_PRIMARY_KEY = 1;
    const TYPE_FOREIGN_KEY = 2;
    const TYPE_UNIQUE = 3;

    protected $name;
    protected $type;
    protected $columns;

    public function __construct($type, $columns, $name = null)
    {
        if (!$this->typeIsValid($type)) {
            throw new Exception('Invalid constraint type supplied.');
        }

        $this->type = $type;
        $this->columns = is_array($columns) ? $columns : array($columns);
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    protected function typeIsValid($type)
    {
        switch ($type) {
            case static::TYPE_PRIMARY_KEY:
            case static::TYPE_FOREIGN_KEY:
            case static::TYPE_UNIQUE:
                return true;
        }

        return false;
    }
}
