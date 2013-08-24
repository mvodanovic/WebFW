<?php

namespace WebFW\Database\Query;

use WebFW\Database\BaseHandler;

class Update extends ConditionalQuery
{
    protected $updateData = array();
    protected $returningColumns = null;
    protected $returningClauseIsSupported;

    public function __construct($tableName)
    {
        parent::__construct($tableName);
        $this->returningClauseIsSupported = BaseHandler::getInstance()->returningClauseIsSupported();
    }

    public function addUpdateData($column, $value)
    {
        $this->updateData[] = $this->escapeIdentifier($column) . ' = ' .  $this->escapeLiteral($value);
    }

    public function addUpdateDataMass(array $updateData)
    {
        foreach ($updateData as $column => $value) {
            $this->addUpdateData($column, $value);
        }
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
        $sql = 'UPDATE ' . $this->tableName . ' SET ' . implode(', ', $this->updateData);

        if (count($this->conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $this->conditions);
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
