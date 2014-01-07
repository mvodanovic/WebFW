<?php

namespace WebFW\Database\Query;

use WebFW\Database\BaseHandler;

class Insert extends Query
{
    protected $columns = null;
    protected $valueSets = null;
    protected $select = null;
    protected $returningColumns = null;
    protected $returningClauseIsSupported;

    public function __construct($tableName)
    {
        parent::__construct($tableName);
        $this->returningClauseIsSupported = BaseHandler::getInstance()->returningClauseIsSupported();
    }

    public function setColumns(array $columns)
    {
        $this->columns = array();
        foreach ($columns as $column) {
            $this->columns[] = $this->escapeIdentifier($column);
        }
    }

    public function addValues(array $values)
    {
        $this->addValueSets(array($values));
    }

    public function addValueSets(array $valueSets)
    {
        $this->select = null;
        if ($this->valueSets === null) {
            $this->valueSets = array();
        }
        foreach ($valueSets as $valueSet) {
            foreach ($valueSet as &$value) {
                $value = $this->escapeLiteral($value);
            }
            $this->valueSets[] = $valueSet;
        }
    }

    public function setSelect(Select $select)
    {
        $this->valueSets = null;
        $this->select = &$select;
    }

    public function setReturningColumns(array $columns)
    {
        $this->returningColumns = array();
        foreach ($columns as $column) {
            list($column, $alias) = explode(' ', $column, 2) + array(null, null);
            $column = $this->escapeIdentifier($column);
            if ($alias !== null) {
                $column .= ' ' . $this->escapeIdentifier($alias);
            }

            $this->returningColumns[] = $column;
        }
    }

    public function getSQL()
    {
        $sql = 'INSERT INTO ' . $this->tableName;

        if ($this->columns !== null) {
            $sql .= ' (' . implode(', ', $this->columns) . ')';
        }

        if ($this->select !== null) {
            $sql .= ' ' . $this->select->getSQL();
        }

        if ($this->valueSets !== null) {
            $valueSetSQLs = array();
            foreach ($this->valueSets as $valueSet) {
                $valueSetSQLs[] = '(' . implode(', ', $valueSet) . ')';
            }

            $sql .= ' VALUES ' . implode(', ', $valueSetSQLs);
        }

        if ($this->returningColumns !== null && $this->returningClauseIsSupported === true) {
            $sql .= ' RETURNING ' . implode(', ', $this->returningColumns);
        }

        if ($this->appendSemicolon === true) {
            $sql .= ';';
        }

        return $sql;
    }
}
