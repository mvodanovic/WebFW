<?php

namespace mvodanovic\WebFW\Database\Query;

use mvodanovic\WebFW\Database\BaseHandler;

class Delete extends ConditionalQuery
{
    protected $returningColumns = null;
    protected $returningClauseIsSupported;

    public function __construct($tableName)
    {
        parent::__construct($tableName);
        $this->returningClauseIsSupported = BaseHandler::getInstance()->returningClauseIsSupported();
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
        $sql = 'DELETE FROM ' . $this->tableName;

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
