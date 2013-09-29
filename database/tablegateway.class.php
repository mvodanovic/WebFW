<?php

namespace WebFW\Database;

use WebFW\Core\Interfaces\iValidate;
use WebFW\Database\BaseHandler;
use WebFW\Database\Table;
use WebFW\Database\TableColumns\Column;
use WebFW\Database\Query\Select;
use WebFW\Database\Query\Join;
use WebFW\Database\Query\Insert;
use WebFW\Database\Query\Update;
use WebFW\Database\Query\Delete;
use WebFW\Core\Exceptions\DBException;
use WebFW\Core\Exception;
use WebFW\Core\ArrayAccess;

abstract class TableGateway extends ArrayAccess implements iValidate
{
    const PRIMARY_KEY_PREFIX = 'pk_';

    protected $table = null;
    protected $recordData = array();
    protected $oldValues = array();
    protected $recordSetIsNew = true;
    protected $additionalData = array();
    protected $validationErrors = array();

    public function __construct()
    {
        if ($this->table === null) {
            throw new Exception('Table not set in table gateway ' . get_class($this));
        } elseif (!($this->table instanceof Table)) {
            throw new Exception('Table not an instance of WebFW\\Database\\Table: ' . $this->table);
        }

        foreach ($this->table->getColumns() as $key => $column) {
            $this->recordData[$key] = $column->getDefaultValue();
            $this->oldValues[$key] = $column->getDefaultValue();
        }
    }

    protected function setTable($table, $namespace = '\\Application\\DBLayer\\Tables\\')
    {
        $table = $namespace . $table;
        if (!class_exists($table)) {
            throw new Exception('Cannot instantiate table: ' . $table);
        }
        $this->table = new $table();
    }

    public function getTable()
    {
        return $this->table;
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    public function loadWithArray(array $values)
    {
        $this->beforeLoad();

        foreach ($values as $key => $value) {
            $this->recordData[$key] = Table::castValueToType($value, $this->table->getColumn($key)->getType());
        }
        $this->oldValues = $this->recordData;

        $this->recordSetIsNew = false;

        $this->afterLoad();
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

        try {
            $this->loadBy(array($primaryKey[0] => $primaryKeyValue));
        } catch (Exception $e) {
            throw new DBException('Error while trying to read data from the database.', $e);
        }
    }

    public function loadBy(array $unique)
    {
        if (empty($unique)) {
            throw new Exception('Unique filter is empty.');
        }

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
            throw new DBException(BaseHandler::getInstance()->getLastError());
        }

        $row = BaseHandler::getInstance()->fetchAssoc($result);
        if ($row === false) {
            throw new DBException(BaseHandler::getInstance()->getLastError());
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

        $this->validateData();
        $this->validateDataUsingTableDefinition();
        if ($this->hasValidationErrors()) {
            return;
        }

        if ($this->recordSetIsNew === true) {
            try {
                $this->saveNew();
            } catch (Exception $e) {
                throw new DBException('Error while trying to insert new data in the database.', $e);
            }
        } else {
            try {
                $this->saveExisting();
            } catch (Exception $e) {
                throw new DBException('Error while trying to update data in the database.', $e);
            }
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
            if ($this->$columnName !== $column->getDefaultValue()) {
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
            throw new DBException(BaseHandler::getInstance()->getLastError());
        }

        if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
            throw new DBException(BaseHandler::getInstance()->getLastError());
        }

        $row = BaseHandler::getInstance()->fetchAssoc($result);
        if ($row === false) {
            throw new DBException(BaseHandler::getInstance()->getLastError());
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
            throw new DBException(BaseHandler::getInstance()->getLastError());
        }

        if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
            throw new DBException(BaseHandler::getInstance()->getLastError());
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

        try {
            $result = BaseHandler::getInstance()->query($sql);
            if ($result === false) {
                throw new DBException(BaseHandler::getInstance()->getLastError());
            }

            if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
                throw new DBException(BaseHandler::getInstance()->getLastError());
            }
        } catch (Exception $e) {
            throw new DBException('Error while trying to delete data from the database.', $e);
        }

        foreach ($this->oldValues as &$value) {
            $value = null;
        }

        $this->recordSetIsNew = true;

        $this->afterDelete();
    }

    public function getValues($appendAdditionalData = false)
    {
        if ($appendAdditionalData) {
            return $this->recordData + $this->additionalData;
        } else {
            return $this->recordData;
        }
    }

    public function getPrimaryKeyColumns()
    {
        return $this->table->getPrimaryKeyColumns();
    }

    public function getPrimaryKeyValues($usePrefix = true)
    {
        $values = array();
        foreach ($this->getPrimaryKeyColumns() as $column) {
            $key = $column;
            if ($usePrefix === true) {
                $key = static::PRIMARY_KEY_PREFIX . $column;
            }
            $values[$key] = $this->$column;
        }

        return $values;
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

    public function offsetExists($offset)
    {
        return isset($this->recordData[$offset]) || isset($this->additionalData[$offset]);
    }

    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->recordData)) {
            return $this->recordData[$offset];
        }

        if (array_key_exists($offset, $this->additionalData)) {
            return $this->additionalData[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        if (array_key_exists($offset, $this->recordData)) {
            $this->recordData[$offset] = Table::castValueToType($value, $this->table->getColumn($offset)->getType());
        } elseif (is_null($offset)) {
            $this->additionalData[] = $value;
        } else {
            $this->additionalData[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (array_key_exists($offset, $this->recordData)) {
            $this->recordData[$offset] = null;
        }

        if (array_key_exists($offset, $this->additionalData)) {
            unset($this->additionalData[$offset]);
        }
    }

    public function validateData() {}

    protected function validateDataUsingTableDefinition()
    {
        foreach ($this->getValues() as $columnName => $value) {
            $column = $this->getTable()->getColumn($columnName);

            if ($value === null) {
                if (!$column->isNullable()) {
                    $this->addValidationError($columnName, 'Field can\'t be empty');
                }
                continue;
            }

            switch ($column->getType())
            {
                case Column::TYPE_BOOLEAN:
                    if (!is_bool($value)) {
                        $this->addValidationError($columnName, 'Value must be true or false');
                    }
                    break;
                case Column::TYPE_INTEGER:
                case Column::TYPE_SMALLINT:
                    if (!is_int($value)) {
                        $this->addValidationError($columnName, 'Value must be an integer');
                    }
                    break;
                case Column::TYPE_FLOAT:
                case Column::TYPE_REAL:
                case Column::TYPE_DOUBLE:
                    if (!is_float($value) && !is_int($value)) {
                        $this->addValidationError($columnName, 'Value must be a number');
                    }
                    break;
                case Column::TYPE_NUMERIC:
                case Column::TYPE_DECIMAL:
                    if (!is_float($value) && !is_int($value)) {
                        $this->addValidationError($columnName, 'Value must be a number');
                    } else {
                        $precision = explode(',', $column->getPrecision());
                        if ($precision[0] === '') {
                            $precision[0] = '18';
                        }
                        if (count($precision) <= 1) {
                            $precision[] = '0';
                        }

                        if ($value < 0) {
                            $value *= -1;
                        }
                        $valueArray = explode('.', (string) $value);
                        if (count($valueArray) <= 1) {
                            $valueArray[] = '';
                        }

                        if (strlen($valueArray[0]) + strlen($valueArray[1]) > (int) $precision[0]) {
                            $this->addValidationError(
                                $columnName,
                                "The number can have a maximum of {$precision[0]} digits"
                            );
                        }

                        if (strlen($valueArray[1]) > (int) $precision[1]) {
                            $this->addValidationError(
                                $columnName,
                                "The number can have a maximum of {$precision[1]} decimal digits"
                            );
                        }
                    }
                    break;
                case Column::TYPE_CHAR:
                case Column::TYPE_VARCHAR:
                case Column::TYPE_NCHAR:
                case Column::TYPE_NVARCHAR:
                    if (!is_string($value)) {
                        $this->addValidationError($columnName, 'Value must be a string');
                    } else {
                        $maxLength = $column->getPrecision();
                        $currentLength = mb_strlen($value);
                        if ($currentLength > $maxLength) {
                            $this->addValidationError(
                                $columnName,
                                "Maximum allowed length is {$maxLength}, currently it is {$currentLength}"
                            );
                        }
                    }
                    break;
            }

        }
    }

    public function addValidationError($field, $error)
    {
        if (!array_key_exists($field, $this->validationErrors)) {
            $this->validationErrors[$field] = array();
        }

        $this->validationErrors[$field][] = $error;
    }

    public function hasValidationErrors()
    {
        return !empty($this->validationErrors);
    }

    public function getValidationErrors($field = null)
    {
        if ($field === null) {
            return $this->validationErrors;
        }

        if (array_key_exists($field, $this->validationErrors)) {
            return $this->validationErrors[$field];
        }

        return array();
    }

    public function clearValidationErrors()
    {
        $this->validationErrors = array();
    }
}
