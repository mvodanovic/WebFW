<?php

namespace WebFW\Database;

use \WebFW\Database\BaseHandler;
use \WebFW\Database\Table;
use \WebFW\Database\Query\Select;
use \WebFW\Database\Query\Join;
use \WebFW\Database\Query\Insert;
use \WebFW\Database\Query\Update;
use \WebFW\Database\Query\Delete;
use \WebFW\Core\Exception;

abstract class TableGateway
{
    protected $table = null;
    protected $recordData = array();
    protected $oldValues = array();
    protected $recordSetIsNew = true;

    public function __construct()
    {
        if ($this->table === null) {
            throw new Exception('Table not set');
        } elseif (!($this->table instanceof Table)) {
            throw new Exception('Set table not instance of \\WebFW\\Database\\Table');
        }

        foreach ($this->table->getColumns() as $key => $column) {
            $this->recordData[$key] = $column->getDefaultValue();
            $this->oldValues[$key] = $column->getDefaultValue();
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

    public function __get($key)
    {
        if (array_key_exists($key, $this->recordData)) {
            return $this->recordData[$key];
        }

        return null;
    }

    public function __set($key, $value) {
        if (array_key_exists($key, $this->recordData)) {
            $this->recordData[$key] = Table::castValueToType($value, $this->table->getColumn($key)->getType());
        }
    }

    public function load($primaryKeyValue)
    {
        $primaryKey = $this->table->getPrimaryKeyColumns();
        if ($primaryKey === null) {
            throw new Exception('Primary key not set.');
        }
        if (count($primaryKey) > 1) {
            throw new Exception('Primary key not simple.');
        }

        $this->loadBy(array($primaryKey[0] => $primaryKeyValue));
    }

    public function loadBy(array $unique)
    {
        $this->beforeLoad();

        $select = new Select($this->table->getName(), $this->table->getAlias());
        $columns = array();
        foreach ($this->table->getColumns() as $column) {
            $columns[] = $column->getName();
        }
        $select->addSelections($columns, $this->table->getAliasedName());
        foreach ($unique as $column => $value) {
            $select->addCondition($column, $value, $this->table->getAliasedName());
        }
        $select->setLimit(1);
        $select->appendSemicolon();

        $sql = $select->getSQL();
        unset($select);

        $result = BaseHandler::getInstance()->query($sql);
        if ($result === false) {
            throw new Exception('Database select failed!');
        }

        $row = BaseHandler::getInstance()->fetchAssoc($result);
        if ($row === false) {
            throw new Exception('Database select failed!');
        }

        foreach ($row as $key => $value) {
            $this->recordData[$key] = Table::castValueToType($value, $this->table->getColumn($key)->getType());
        }
        $this->oldValues = $this->recordData;

        $this->recordSetIsNew = false;

        $this->afterLoad();
    }

    public function save()
    {
        $this->beforeSave();

        if ($this->recordSetIsNew === true) {
            $this->saveNew();
        } else {
            $this->saveExisting();
        }

        $this->afterSave();
    }

    public function saveNew()
    {
        $this->beforeSaveNew();

        $insert = new Insert($this->table->getName());
        $columns = array();
        $values = array();
        $primaryKeyColumns = $this->table->getPrimaryKeyColumns();
        foreach ($this->table->getColumns() as $column) {
            $columnName = $column->getName();
            if (!in_array($columnName, $primaryKeyColumns)) {
                $columns[] = $columnName;
                $values[] = $this->$columnName;
            }
        }
        $insert->setColumns($columns);
        $insert->addValues($values);
        $insert->setReturningColumns($primaryKeyColumns);
        $insert->appendSemicolon();

        $sql = $insert->getSQL();
        unset($insert);

        $result = BaseHandler::getInstance()->query($sql);
        if ($result === false) {
            throw new Exception('Database insert failed!');
        }

        if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
            throw new Exception('Database insert failed!');
        }

        $row = BaseHandler::getInstance()->fetchAssoc($result);
        if ($row === false) {
            throw new Exception('Database insert failed!');
        }

        foreach ($row as $key => $value) {
            $this->recordData[$key] = Table::castValueToType($value, $this->table->getColumn($key)->getType());
        }

        $this->oldValues = $this->recordData;

        $this->recordSetIsNew = false;

        $this->afterSaveNew();
    }

    public function saveExisting()
    {
        $this->beforeSaveExisting();

        $updatesExist = false;
        $update = new Update($this->table->getName());
        foreach ($this->recordData as $column => $value) {
            if ($this->$column === $this->oldValues[$column]) {
                continue;
            }
            $updatesExist = true;
            $update->addUpdateData($column, $value);
        }
        if (!$updatesExist) {
            return;
        }
        foreach ($this->table->getPrimaryKeyColumns() as $column) {
            $update->addCondition($column, $this->$column);
        }
        $update->appendSemicolon();

        $sql = $update->getSQL();
        unset($update);

        $result = BaseHandler::getInstance()->query($sql);
        if ($result === false) {
            throw new Exception('Database update failed!');
        }

        if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
            throw new Exception('Database update failed!');
        }

        $this->oldValues = $this->recordData;

        $this->recordSetIsNew = false;

        $this->afterSaveExisting();
    }

    public function delete()
    {
        $this->beforeDelete();

        if ($this->recordSetIsNew === true) {
            return;
        }

        $delete = new Delete($this->table->getName());
        foreach ($this->table->getPrimaryKeyColumns() as $column) {
            $delete->addCondition($column, $this->$column);
        }
        $delete->appendSemicolon();

        $sql = $delete->getSQL();
        unset($delete);

        $result = BaseHandler::getInstance()->query($sql);
        if ($result === false) {
            throw new Exception('Database delete failed!');
        }

        if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
            throw new Exception('Database delete failed!');
        }

        foreach ($this->oldValues as &$value) {
            $value = null;
        }

        $this->recordSetIsNew = true;

        $this->afterDelete();
    }

    public function getValues()
    {
        return $this->recordData;
    }

    protected function beforeLoad() {}
    protected function afterLoad() {}
    protected function beforeSave() {}
    protected function afterSave() {}
    protected function beforeSaveNew() {}
    protected function afterSaveNew() {}
    protected function beforeSaveExisting() {}
    protected function afterSaveExisting() {}
    protected function beforeDelete() {}
    protected function afterDelete() {}
}
