<?php
namespace WebFW\Database;

use WebFW\Database\BaseHandler;
use WebFW\Database\TableColumns\Column;
use WebFW\Database\TableConstraints\Constraint;
use WebFW\Database\TableConstraints\PrimaryKey;
use WebFW\Core\Exception;

abstract class Table
{
    protected $name = null;
    protected $alias = null;
    protected $columns = array();
    protected $constraints = array();
    protected $primaryKeyConstraintIndex = null;

    const INDEX_PLAIN = 1;
    const INDEX_UNIQUE = 2;
    const INDEX_FULLTEXT = 3;

    protected function setName($name, $alias = null)
    {
        $this->name = $name;
        $this->alias = $alias;
    }

    protected function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = &$column;
    }

    protected function addConstraint(Constraint $constraint)
    {
        $this->constraints[] = &$constraint;

        if ($constraint->getType() === Constraint::TYPE_PRIMARY_KEY) {
            if ($this->primaryKeyConstraintIndex !== null) {
                throw new Exception('Primary key is already set.');
            }

            end($this->constraints);
            $this->primaryKeyConstraintIndex = key($this->constraints);
            reset($this->constraints);
        }
    }

    /**
     * @param $constraintFieldNames
     * @return null|Constraint
     */
    public function getConstraint($constraintFieldNames)
    {
        if (!is_array($constraintFieldNames)) {
            $constraintFieldNames = array($constraintFieldNames);
        }

        foreach ($this->constraints as $constraint) {
            /** @var $constraint Constraint */
            if (count ($constraintFieldNames) !== count($constraint->getColumns())) {
                continue;
            }
            $isMatch = true;
            foreach ($constraint->getColumns() as $column) {
                if (!in_array($column, $constraintFieldNames)) {
                    $isMatch = false;
                    break;
                }
            }
            if ($isMatch) {
                return $constraint;
            }
        }

        return null;
    }

    /// TODO: implement
    protected function addIndex()
    {

    }

    public function getName()
    {
        return $this->name;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getAliasedName()
    {
        return $this->alias !== null ? $this->alias : $this->name;
    }

    public function getPrimaryKeyColumns()
    {
        if ($this->primaryKeyConstraintIndex === null) {
            return null;
        }

        /** @var $primaryKey PrimaryKey */
        $primaryKey = &$this->constraints[$this->primaryKeyConstraintIndex];
        return $primaryKey->getColumns();
    }

    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param $columnName
     * @return Column
     */
    public function getColumn($columnName)
    {
        if (!array_key_exists($columnName, $this->columns)) {
            return null;
        }

        return $this->columns[$columnName];
    }

    public function hasColumn($columnName)
    {
        return array_key_exists($columnName, $this->columns);
    }

    public static function castValueToType($value, $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case Column::TYPE_VARCHAR:
            case Column::TYPE_CHAR:
            case Column::TYPE_NVARCHAR:
            case Column::TYPE_NCHAR:
                return (string) $value;
            case Column::TYPE_INTEGER:
            case Column::TYPE_SMALLINT:
                return (int) $value;
            case Column::TYPE_BOOLEAN:
                return (bool) BaseHandler::getInstance()->convertBoolean($value);
            case Column::TYPE_DECIMAL:
            case Column::TYPE_DOUBLE:
            case Column::TYPE_FLOAT:
            case Column::TYPE_NUMERIC:
            case Column::TYPE_REAL:
                return (float) $value;
            default:
                return $value;
        }
    }
}
