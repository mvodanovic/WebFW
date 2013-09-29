<?php

namespace WebFW\Database;

use WebFW\Core\Exception;
use WebFW\Core\Exceptions\DBException;
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
            throw new Exception('Table not set in list fetcher ' . get_class($this));
        } elseif (!($this->table instanceof Table)) {
            throw new Exception('Table not an instance of WebFW\\Database\\Table: ' . $this->table);
        }

        foreach ($this->table->getPrimaryKeyColumns() as $column) {
            $this->sort[$column] = 'DESC';
        }
    }

    protected function setTable($table, $namespace = '\\Application\\DBLayer\\Tables\\')
    {
        $table = $namespace . $table;
        $this->table = new $table;
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
            throw new DBException(
                'Error while trying to read data from the database.',
                new DBException(BaseHandler::getInstance()->getLastError())
            );
        }

        $list = BaseHandler::getInstance()->fetchAll($result);

        if ($this->getObjectList && $this->tableGateway !== null) {
            $objectsList = array();
            foreach ($list as &$row) {
                $object = new $this->tableGateway;
                if (!($object instanceof TableGateway)) {
                    throw new Exception(
                        'Table gateway not an instance of WebFW\\Database\\TableGateway: ' . $this->tableGateway
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
            throw new DBException(
                'Error while trying to read data from the database.',
                new DBException(BaseHandler::getInstance()->getLastError())
            );
        }

        $row = BaseHandler::getInstance()->fetchAssoc($result);
        if ($row === false) {
            throw new DBException(
                'Error while trying to read data from the database.',
                new DBException(BaseHandler::getInstance()->getLastError())
            );
        }

        return (int) $row['cnt'];
    }

    public function setGetObjectListFlag($flag)
    {
        $this->getObjectList = (boolean) $flag;
    }
}
