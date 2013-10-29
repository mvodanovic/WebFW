<?php

namespace WebFW\Database;

use WebFW\Core\Exception;
use WebFW\Core\Exceptions\DBException;
use WebFW\Database\Query\Join;
use WebFW\Database\TableConstraints\ForeignKey;
use WebFW\Database\TableColumns\Column;
use WebFW\Database\Query\Select;

abstract class ListFetcher
{
    /** @var Table  */
    protected $table = null;
    /** @var TableGateway  */
    protected $tableGateway = null;
    protected $filter = array();
    protected $sort = array();
    protected $limit = 50;
    protected $offset = 0;
    protected $getObjectList = true;
    protected $objectClassName = null;
    protected $tableJoins = array();

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
        if (!class_exists($table)) {
            throw new Exception(
                'Class doesn\'t exist: ' . $table
            );
        }
        /** @var table Table */
        $this->table = new $table;
    }

    /**
     * @return null|Table
     */
    public function getTable()
    {
        return $this->table;
    }

    protected function setTableGateway($tableGateway, $namespace = '\\Application\\DBLayer\\')
    {
        $tableGateway = $namespace . $tableGateway;
        if (!class_exists($tableGateway)) {
            throw new Exception(
                'Class doesn\'t exist: ' . $tableGateway
            );
        }
        /** @var tableGateway TableGateway */
        $this->tableGateway = new $tableGateway();
        if (!($this->tableGateway instanceof TableGateway)) {
            throw new Exception(
                'Table gateway not an instance of WebFW\\Database\\TableGateway: ' . $tableGateway
            );
        }
    }

    /**
     * @return null|TableGateway
     */
    public function getTableGateway()
    {
        return $this->tableGateway;
    }

    protected function addJoin($tableFieldNames, $table, $namespace = '\\Application\\DBLayer\\Tables\\', $joinType = Join::TYPE_INNER)
    {
        $table = $namespace . $table;
        if (!class_exists($table)) {
            throw new Exception('Cannot instantiate table: ' . $table);
        }
        /** @var $tableInstance Table */
        $tableInstance = new $table();
        if (!($tableInstance instanceof Table)) {
            throw new Exception('Class ' . $table . ' not an instance of WebFW\\Database\\Table');
        }

        $foreignKey = $tableInstance->getConstraint($tableFieldNames);
        if (!($foreignKey instanceof ForeignKey)) {
            throw new Exception('No foreign keys defined on table ' . $table . ' with fields: ' . implode(', ', $tableFieldNames));
        }
        $this->tableJoins[] = array(
            'table' => $tableInstance,
            'foreignKey' => $foreignKey,
            'joinType' => $joinType,
        );
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
            /** @var $column Column */
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

        foreach ($this->tableJoins as &$joinDef) {
            /** @var $foreignTable Table */
            $foreignTable = &$joinDef['table'];
            /** @var $foreignKey ForeignKey */
            $foreignKey = &$joinDef['foreignKey'];
            $join = new Join($foreignTable->getName(), $foreignTable->getAlias(), $joinDef['joinType']);
            foreach ($foreignKey->getReferences() as $column => $foreignColumn) {
                $join->addJoinTerm(
                    $this->table->getAliasedName(),
                    $column,
                    $foreignTable->getAliasedName(),
                    $foreignColumn
                );
            }
            $select->addJoin($join);
            $columnNames = array();
            $columnAliases = array();
            foreach ($foreignTable->getColumns() as $column) {
                /** @var $column Column */
                $columnNames[] = $column->getName();
                $columnAliases[] = $foreignTable->getAliasedName() . '_' . $column->getName();
            }
            $select->addSelections($columnNames, $foreignTable->getAliasedName(), $columnAliases);
        }

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

        if ($this->getObjectList && $this->tableGateway instanceof TableGateway) {
            $objectsList = array();
            foreach ($list as &$row) {
                $object = clone $this->tableGateway;
                $object->loadWithArray($row, false);
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

        foreach ($this->tableJoins as &$joinDef) {
            /** @var $foreignTable Table */
            $foreignTable = &$joinDef['table'];
            /** @var $foreignKey ForeignKey */
            $foreignKey = &$joinDef['foreignKey'];
            $join = new Join($foreignTable->getName(), $foreignTable->getAlias(), $joinDef['joinType']);
            foreach ($foreignKey->getReferences() as $column => $foreignColumn) {
                $join->addJoinTerm(
                    $this->table->getAliasedName(),
                    $column,
                    $foreignTable->getAliasedName(),
                    $foreignColumn
                );
            }
            $select->addJoin($join);
        }

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
