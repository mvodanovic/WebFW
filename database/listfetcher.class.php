<?php

namespace WebFW\Database;

use WebFW\Core\Exception;
use WebFW\Database\BaseHandler;
use WebFW\Database\Table;
use WebFW\Database\Query\Select;

abstract class ListFetcher
{
    protected $table = null;
    protected $tableGateway = null;
    protected $filter = array();
    protected $sort = array();
    protected $limit = 50;
    protected $offset = 0;
    protected $getObjectList = true;
    protected $objectClassName = null;

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
            throw new Exception("Class {$able} is not derived from \\WebFW\\Database\\Table");
        }
    }

    protected function setTableGateway($tableGateway, $namespace = '\\Application\\DBLayer\\')
    {
        $this->tableGateway = $namespace . $tableGateway;
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

        $list = BaseHandler::getInstance()->fetchAll($result);

        if ($this->getObjectList && $this->tableGateway !== null) {
            $objectsList = array();
            foreach ($list as &$row) {
                $object = new $this->tableGateway;
                if (!($object instanceof TableGateway)) {
                    throw new Exception(
                        "Class {$this->tableGateway} is not derived from WebFW\\Database\\TableGateway"
                    );
                }
                $object->loadWithArray($row);
                $objectsList[] = $object;
            }
            $list = &$objectsList;
        }

        return $list;
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

    public function setGetObjectListFlag($flag)
    {
        $this->getObjectList = (boolean) $flag;
    }
}
