<?php

namespace mvodanovic\WebFW\Database\Query;

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

    /**
     * @param $columns - a single column or an array of columns
     * @param $values - an array of values, array of arrays if $columns is an array
     * @param null $tableAlias
     * @param bool $prefixWithNot - use NOT IN or IN
     * @param bool $prefixWithOr
     */
    public function addInCondition($columns, $values, $tableAlias = null, $prefixWithNot = false, $prefixWithOr = false)
    {
        $condition = '';

        if ($tableAlias !== null) {
            $tableAlias = $this->escapeIdentifier($tableAlias) . '.';
        }

        if (is_array($columns)) {
            foreach ($columns as &$column) {
                $column = $tableAlias . $this->escapeIdentifier($column);
            }
            $condition .= '('  . implode(', ', $columns) . ')';
        } else {
            $condition .= $tableAlias . $this->escapeIdentifier($columns);
        }

        if ($prefixWithNot === true) {
            $condition .= ' NOT';
        }

        $condition .= ' IN ';

        foreach ($values as &$valueSet) {
            if (is_array($valueSet)) {
                foreach ($valueSet as &$value) {
                    if ($value === null) {
                        $value = 'NULL';
                    } elseif ($value instanceof Select) {
                        $value = $value->getSQL();
                    } else {
                        $value = $this->escapeLiteral($value);
                    }
                }
                $valueSet = '(' .implode(', ', $valueSet) . ')';
            } else {
                if ($valueSet === null) {
                    $valueSet = 'NULL';
                } elseif ($valueSet instanceof Select) {
                    $valueSet = $valueSet->getSQL();
                } else {
                    $valueSet = $this->escapeLiteral($valueSet);
                }
            }
        }

        $condition .= '(' . implode(', ', $values) . ')';

        $this->addRawCondition($condition, $prefixWithOr);
    }
}
