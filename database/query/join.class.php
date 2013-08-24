<?php

namespace WebFW\Database\Query;

use WebFW\Database\BaseHandler;
use WebFW\Core\Exception;

class Join
{
    const TYPE_INNER = 1;
    const TYPE_LEFT = 2;
    const TYPE_RIGHT = 3;
    const TYPE_FULL = 4;
    const TYPE_CROSS = 5;

    protected $tableName;
    protected $tableAlias;
    protected $type;
    protected $joinTerms = null;
    protected $usings = null;
    protected $naturalJoin = false;

    public function __construct($tableName, $tableAlias = null, $type = self::TYPE_INNER)
    {
        if (!$this->joinTypeIsValid($type)) {
            throw new Exception('Invalid join type.');
        }

        $this->tableName = BaseHandler::getInstance()->escapeIdentifier($tableName);
        $this->tableAlias = $tableAlias === null ? $tableAlias : BaseHandler::getInstance()->escapeIdentifier($tableAlias);
        $this->type = $type;
    }

    public function addJoinTerm($table1, $column1, $table2, $column2)
    {
        if ($this->type === static::TYPE_CROSS) {
            return;
        }

        if ($this->joinTerms === null) {
            $this->joinTerms = array();
        }

        $this->joinTerms[] = array(
            'table1' => BaseHandler::getInstance()->escapeIdentifier($table1),
            'column1' => BaseHandler::getInstance()->escapeIdentifier($column1),
            'table2' => BaseHandler::getInstance()->escapeIdentifier($table2),
            'column2' => BaseHandler::getInstance()->escapeIdentifier($column2),
        );

        $this->usings = null;
        $this->naturalJoin = false;
    }

    public function setUsings($columns)
    {
        if ($this->type === static::TYPE_CROSS || count($columns) === 0) {
            return;
        }

        $this->usings = array();
        foreach ($columns as $column) {
            $this->usings[] = BaseHandler::getInstance()->escapeIdentifier($column);
        }

        $this->joinTerms = null;
        $this->naturalJoin = false;
    }

    public function setNaturalJoin()
    {
        if ($this->type === static::TYPE_CROSS) {
            return;
        }

        $this->naturalJoin = true;
        $this->joinTerms = null;
        $this->usings = null;
    }

    protected function joinTypeIsValid($type)
    {
        switch ($type) {
            case static::TYPE_INNER:
            case static::TYPE_LEFT:
            case static::TYPE_RIGHT:
            case static::TYPE_FULL:
            case static::TYPE_CROSS:
                return true;
        }

        return false;
    }

    public function getSQL()
    {
        $sql = '';

        if ($this->naturalJoin === true) {
            $sql .= ' NATURAL';
        }

        switch ($this->type) {
            case static::TYPE_LEFT:
                $sql .= ' LEFT';
            case static::TYPE_RIGHT:
                $sql .= ' RIGHT';
            case static::TYPE_FULL:
                $sql .= ' FULL';
            case static::TYPE_CROSS:
                $sql .= ' CROSS';
        }

        $sql .= ' JOIN ' . $this->tableName;

        if ($this->tableAlias !== null) {
            $sql .= ' ' . $this->tableAlias;
        }

        if ($this->joinTerms !== null) {
            $termsSQL = array();
            foreach ($this->joinTerms as $item) {
                $termsSQL[] =
                    $item['table1'] . '.' .
                    $item['column1'] . ' = ' .
                    $item['table2'] . '.' .
                    $item['column2']
                ;
            }

            $sql .= ' ON ' . implode(' AND ', $termsSQL);
        }

        if ($this->usings !== null) {
            $sql .= ' USING (' . implode(', ', $this->usings) . ')';
        }

        return $sql;
    }
}
