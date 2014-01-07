<?php

namespace WebFW\Database;

use WebFW\Cache\Cache;
use WebFW\Cache\Classes\Cacheable;
use WebFW\Cache\Classes\CacheGroupHelper;
use WebFW\Core\Classes\BaseClass;
use WebFW\Core\Exception;
use WebFW\Core\Exceptions\DBException;
use WebFW\Database\Query\Join;
use WebFW\Database\TableConstraints\ForeignKey;
use WebFW\Database\TableColumns\Column;
use WebFW\Database\Query\Select;

abstract class ListFetcher extends BaseClass
{
    use Cacheable {
        getCacheExpirationTime as private getCacheExpirationTimeFromCacheable;
        isCacheEnabled as private isCacheEnabledFromCacheable;
    }

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
            throw new Exception('Table not set in list fetcher ' . static::className());
        } elseif (!($this->table instanceof Table)) {
            throw new Exception('Table not an instance of ' . Table::className() . ': ' . $this->table);
        }

        foreach ($this->table->getPrimaryKeyColumns() as $column) {
            $this->sort[$column->getName()] = 'DESC';
        }
    }

    protected function setTable(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @return null|Table
     */
    public function getTable()
    {
        return $this->table;
    }

    protected function setTableGateway($tableGateway)
    {
        if (!class_exists($tableGateway)) {
            throw new Exception(
                'Class doesn\'t exist: ' . $tableGateway
            );
        }
        /** @var tableGateway TableGateway */
        $this->tableGateway = new $tableGateway();
        if (!($this->tableGateway instanceof TableGateway)) {
            throw new Exception(
                'Table gateway not an instance of ' . TableGateway::className() . ': ' . $tableGateway
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

    protected function addJoin(ForeignKey $foreignKey, $joinType = Join::TYPE_INNER)
    {
        $this->tableJoins[] = array(
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

        if ($this->isCacheEnabled()) {
            $cacheKey = $this->getListCacheKey($filter, $sort, $limit, $offset);
            if (Cache::getInstance()->exists($cacheKey)) {
                return Cache::getInstance()->get($cacheKey);
            }
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
            /** @var $foreignKey ForeignKey */
            $foreignKey = &$joinDef['foreignKey'];
            $foreignTable = $foreignKey->getReferencedTable();
            $join = new Join($foreignTable->getName(), $foreignTable->getAlias(), $joinDef['joinType']);
            foreach ($foreignKey->getReferences() as $columns) {
                /** @var Column $localColumn */
                $localColumn = $columns['local'];
                /** @var Column $referencedColumn */
                $referencedColumn = $columns['referenced'];
                $join->addJoinTerm(
                    $this->table->getAliasedName(),
                    $localColumn->getName(),
                    $foreignTable->getAliasedName(),
                    $referencedColumn->getName()
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

        if ($this->isCacheEnabled()) {
            $cacheKey = $this->getListCacheKey($filter, $sort, $limit, $offset);
            Cache::getInstance()->set($cacheKey, $list, $this->getCacheExpirationTime());
            CacheGroupHelper::append($this->table->className(), $cacheKey, $this->table->getCacheExpirationTime());
        }

        return $list;
    }

    public function getCount($filter = null)
    {
        if ($filter === null) {
            $filter = $this->filter;
        }

        if ($this->isCacheEnabled()) {
            $cacheKey = $this->getCountCacheKey($filter);
            if (Cache::getInstance()->exists($cacheKey)) {
                return Cache::getInstance()->get($cacheKey);
            }
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
                new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql))
            );
        }

        $row = BaseHandler::getInstance()->fetchAssoc($result);
        if ($row === false) {
            throw new DBException(
                'Error while trying to read data from the database.',
                new DBException(BaseHandler::getInstance()->getLastError())
            );
        }

        $count = (int) $row['cnt'];

        if ($this->isCacheEnabled()) {
            $cacheKey = $this->getCountCacheKey($filter);
            Cache::getInstance()->set($cacheKey, $count, $this->getCacheExpirationTime());
            CacheGroupHelper::append($this->table->className(), $cacheKey, $this->table->getCacheExpirationTime());
        }

        return $count;
    }

    public function setGetObjectListFlag($flag)
    {
        $this->getObjectList = (boolean) $flag;
    }

    public function getCacheExpirationTime()
    {
        $expirationTime = static::getCacheExpirationTimeFromCacheable();
        if ($expirationTime === null) {
            $expirationTime = $this->table->getCacheExpirationTime();
        }

        return $expirationTime;
    }

    public function isCacheEnabled()
    {
        $isCacheEnabled = static::isCacheEnabledFromCacheable();
        if ($isCacheEnabled === false) {
            $isCacheEnabled = $this->table->isCacheEnabled();
        }

        return $isCacheEnabled;
    }

    protected function getListCacheKey($filter, $sort, $limit, $offset)
    {
        $cacheKey = static::className();
        $cacheKey .= serialize($filter);
        $cacheKey .= serialize($sort);
        $cacheKey .= serialize($limit);
        $cacheKey .= serialize($offset);

        return $cacheKey;
    }

    protected function getCountCacheKey($filter)
    {
        return static::className() . serialize($filter);
    }
}
