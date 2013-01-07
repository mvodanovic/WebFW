<?php

namespace WebFW\Database;

use \WebFW\Database\BaseHandler;
use \WebFW\Database\Table;
use \WebFW\Database\Query\Select;

abstract class ListFetcher
{
    protected $table = null;
    protected $filter = array();
    protected $sort = array();
    protected $limit = 50;
    protected $offset = 0;

    public function __construct()
    {
        if ($this->table === null) {
            throw new Exception('Table not set!');
        } elseif (!($this->table instanceof Table)) {
            throw new Exception('Set table not instance of \\WebFW\\Database\\Table');
        }

        foreach ($this->table->getPrimaryKeyColumns() as $column) {
            $this->sort[$column] = 'DESC';
        }
    }

    protected function setTable($table, $namespace = '\\Application\\DBLayer\\Tables\\')
    {
        $table = $namespace . $table;
        $this->table = new $table;

        if (!($this->table instanceof Table)) {
            throw new Exception('Invalid database table: ' . $namespace . $table);
        }
    }

    public function getList($filter = null, $sort = null, $limit = null, $offset = null)
    {
        if ($filter === null) {
            $filter = $this->filter;
        }

        if ($sort === null) {
            $sort = $this->sort;
        }

        if ($limit === null) {
            $limit = $this->limit;
        }

        if ($offset === null) {
            $offset = $this->offset;
        }

        $select = new Select($this->table->getName(), $this->table->getAlias());
        $columns = array();
        foreach ($this->table->getColumns() as $column) {
            $columns[] = $column->getName();
        }
        $select->addSelections($columns, $this->table->getAliasedName());
        foreach ($filter as $column => $value) {
            if ($this->table->hasColumn($column)) {
                $select->addCondition($column, $value, $this->table->getAliasedName());
            }
        }
        $select->addSorts($sort);
        $select->setLimit($limit, $offset);
        $select->appendSemicolon();

        $sql = $select->getSQL();
        unset($select);

        $result = BaseHandler::getInstance()->query($sql);
        if ($result === false) {
            throw new Exception('Database query error!');
        }

        return BaseHandler::getInstance()->fetchAll($result);
    }

    public function getCount($filter = null)
    {
        if ($filter === null) {
            $filter = $this->filter;
        }

        $select = new Select($this->table->getName(), $this->table->getAlias());
        $select->addRawSelection('COUNT(*)', 'cnt');
        foreach ($filter as $column => $value) {
            if ($this->table->hasColumn($column)) {
                $select->addCondition($column, $value, $this->table->getAliasedName());
            }
        }
        $select->appendSemicolon();

        $sql = $select->getSQL();
        unset($select);

        $result = BaseHandler::getInstance()->query($sql);
        if ($result === false) {
            throw new Exception('Database query error!');
        }

        $row = BaseHandler::getInstance()->fetchAssoc($result);
        if ($row === false) {
            throw new Exception('Database query error!');
        }

        return (int) $row['cnt'];
    }
}
