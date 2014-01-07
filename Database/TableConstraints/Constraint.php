<?php

namespace WebFW\Database\TableConstraints;

use WebFW\Core\Exception;
use WebFW\Database\Table;
use WebFW\Database\TableColumns\Column;

abstract class Constraint
{
    const TYPE_PRIMARY_KEY = 1;
    const TYPE_FOREIGN_KEY = 2;
    const TYPE_UNIQUE = 3;

    protected $table;
    protected $name;
    protected $type;
    protected $columns = array();

    /**
     * @return static
     */
    public static function spawn()
    {
        $rc = new \ReflectionClass(get_called_class());
        return $rc->newInstanceArgs(func_get_args());
    }

    public function __construct(Table $table, $type, $name = null)
    {
        if (!$this->typeIsValid($type)) {
            throw new Exception('Invalid constraint type supplied.');
        }

        $this->table = $table;
        $this->type = $type;
        $this->name = $name;
    }

    public function addColumn(Column $column)
    {
        $this->columns[] = $column;

        return $this;
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
