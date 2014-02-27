<?php

namespace mvodanovic\WebFW\Database\Query;

use mvodanovic\WebFW\Database\BaseHandler;

class Select extends ConditionalQuery
{
    protected $tableAlias;
    protected $selectColumns = array();
    protected $joins = array();
    protected $groups = array();
    protected $havings = array();
    protected $sorts = array();
    protected $limit = null;
    protected $offset = null;

    public function __construct($tableName, $tableAlias = null)
    {
        parent::__construct($tableName);
        $this->tableAlias = $tableAlias === null ? null : $this->escapeIdentifier($tableAlias);
    }

    public function addSelections($columns, $tableAlias = null, $columnAliases = array())
    {
        if ($tableAlias !== null) {
            $tableAlias = $this->escapeIdentifier($tableAlias);
        }

        foreach ($columns as $i => $column) {
            if ($column !== '*') {
                $column = $this->escapeIdentifier($column);
            }

            $this->selectColumns[] = array(
                'column' => $column,
                'tableAlias' => $tableAlias,
                'columnAlias' => array_key_exists($i, $columnAliases) ? $this->escapeIdentifier($columnAliases[$i]) : null,
            );
        }
    }

    public function addRawSelection($selection, $alias = null)
    {
        $this->selectColumns[] = array(
            'column' => $selection,
            'tableAlias' => null,
            'columnAlias' => $alias === null ? null : $this->escapeIdentifier($alias),
        );
    }

    public function addJoin(Join $join)
    {
        $this->joins[] = &$join;
    }

    public function addGrouping(array $columns)
    {
        foreach ($columns as $column) {
            $this->groups[] = $this->escapeIdentifier($column);
        }
    }

    public function addHaving($expression, $prefixWithOr = false)
    {
        if ($prefixWithOr === true && count($this->havings) > 0) {
            end($this->havings);
            $key = key($this->havings);
            reset($this->havings);

            $expression = $this->havings[$key] . ' OR ' . $expression;
            unset($this->havings[$key]);
        }

        $this->havings[] = $expression;
    }

    public function addSorts(array $sorts) {
        foreach ($sorts as $column => $sort) {
            $this->sorts[] = $this->escapeIdentifier($column) . ' ' . $sort;
        }
    }

    public function setLimit($limit, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getSQL()
    {
        $sql = 'SELECT';

        $selectionsSQL = array();
        foreach ($this->selectColumns as $item) {
            $selectionSQL = '';
            if ($item['tableAlias'] !== null) {
                $selectionSQL .= $item['tableAlias'] . '.';
            }
            $selectionSQL .= $item['column'];
            if ($item['columnAlias'] !== null && $item['column'] !== '*') {
                $selectionSQL .= ' ' . $item['columnAlias'];
            }

            $selectionsSQL[] = $selectionSQL;
        }

        $sql .= ' ' . implode(', ', $selectionsSQL);

        $sql .= ' FROM ' . $this->tableName;
        if ($this->tableAlias !== null) {
            $sql .= ' ' . $this->tableAlias;
        }

        foreach ($this->joins as &$join) {
            /** @var $join Join */
            $sql .= $join->getSQL();
        }

        if (count($this->conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $this->conditions);
        }

        if (count($this->groups) > 0) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        if (count($this->havings) > 0) {
            $sql .= ' HAVING ' . implode(' AND ', $this->havings);
        }

        if (count($this->sorts) > 0) {
            $sql .= ' ORDER BY ' . implode(', ', $this->sorts);
        }

        $limit = BaseHandler::getInstance()->getLimitAndOffset($this->limit, $this->offset);
        if ($limit !== '') {
            $sql .= ' ' . $limit;
        }

        if ($this->appendSemicolon === true) {
            $sql .= ';';
        }

        return $sql;
    }
}
