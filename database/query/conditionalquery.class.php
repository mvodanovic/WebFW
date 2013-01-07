<?php

namespace WebFW\Database\Query;

abstract class ConditionalQuery extends Query
{
    protected $conditions = array();

    public function addRawCondition($condition, $prefixWithOr = false)
    {
        if ($prefixWithOr === true && count($this->conditions) > 0) {
            end($this->conditions);
            $key = key($this->conditions);
            reset($this->conditions);

            $condition = $this->conditions[$key] . ' OR ' . $condition;
            unset($this->conditions[$key]);
        }

        $this->conditions[] = $condition;
    }

    public function addCondition($column, $value, $tableAlias = null, $operator = '=', $prefixWithOr = false)
    {
        $condition = '';
        if ($tableAlias !== null) {
            $condition .= $this->escapeIdentifier($tableAlias) . '.';
        }
        if ($value === null) {
            if ($operator === '=') {
                $condition .= $this->escapeIdentifier($column) . ' IS NULL';
            } else {
                $condition .= $this->escapeIdentifier($column) . ' IS NOT NULL';
            }
        } else if ($value instanceof Select) {
            $condition .= $this->escapeIdentifier($column) . ' ' . $operator . ' (' . $value->getSQL() . ')';
        } else {
            $condition .= $this->escapeIdentifier($column) . ' ' . $operator . ' ' . $this->escapeLiteral($value);
        }

        $this->addRawCondition($condition, $prefixWithOr);
    }
}
