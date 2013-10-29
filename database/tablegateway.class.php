<?php

namespace WebFW\Database;

use WebFW\Core\Interfaces\iValidate;
use WebFW\Database\TableColumns\Column;
use WebFW\Database\TableConstraints\ForeignKey;
use WebFW\Database\Query\Select;
use WebFW\Database\Query\Insert;
use WebFW\Database\Query\Update;
use WebFW\Database\Query\Delete;
use WebFW\Core\Exceptions\DBException;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Core\Exception;
use WebFW\Core\ArrayAccess;

abstract class TableGateway extends ArrayAccess implements iValidate
{
    const PRIMARY_KEY_PREFIX = 'pk_';

    /** @var Table */
    protected $table = null;
    protected $recordData = array();
    protected $oldValues = array();
    protected $recordSetIsNew = true;
    protected $additionalData = array();
    protected $validationErrors = array();
    protected $foreignListFetchers = array();
    protected $useForeignListFetchers = true;
    protected $isTransactionStarted = false;

    public function __construct()
    {
        if ($this->table === null) {
            throw new Exception('Table not set in table gateway ' . get_class($this));
        } elseif (!($this->table instanceof Table)) {
            throw new Exception('Table not an instance of WebFW\\Database\\Table: ' . $this->table);
        }

        foreach ($this->table->getColumns() as $key => $column) {
            /** @var $column Column */
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
        if (!($this->table instanceof Table)) {
            throw new Exception('Class ' . $table . ' not an instance of WebFW\\Database\\Table');
        }
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Adds a foreign list fetcher to the table gateway.
     *
     * @note The list fetcher is used for fetching lists of data from dependant tables.
     * @note The list fetcher must have a table gateway defined, the list will be populated with table gateways.
     * @note The list fetcher's table must have a foreign key referencing this table gateway's table.
     *
     * @param string $collectionFieldName - name under which the list will be stored in the table gateway
     * @param array|string $tableFieldNames - name of dependant table's fields which compose the foreign key
     * @param string $listFetcher - name of the list fetcher
     * @param string $namespace - namespace of the list fetcher
     * @throws \WebFW\Core\Exception - if called with invalid parameters
     * @see useForeignListFetchers
     */
    public function addForeignListFetcher($collectionFieldName, $tableFieldNames, $listFetcher, $namespace = '\\Application\\DBLayer\\ListFetchers\\')
    {
        $listFetcher = $namespace . $listFetcher;
        if (!class_exists($listFetcher)) {
            throw new Exception('Cannot instantiate list fetcher: ' . $listFetcher);
        }
        /** @var $listFetcherInstance ListFetcher */
        $listFetcherInstance = new $listFetcher();
        if (!($listFetcherInstance instanceof ListFetcher)) {
            throw new Exception('Class ' . $listFetcher . ' not an instance of WebFW\\Database\\ListFetcher');
        }

        if (!($listFetcherInstance->getTableGateway() instanceof TableGateway)) {
            throw new Exception('Foreign list fetcher ' . $listFetcher . ' has no table gateway specified');
        }

        $foreignKey = $listFetcherInstance->getTable()->getConstraint($tableFieldNames);
        if (!($foreignKey instanceof ForeignKey)) {
            throw new Exception('No foreign keys defined for list fetcher ' . $listFetcher . ' with fields: ' . implode(', ', $tableFieldNames));
        }

        $this->foreignListFetchers[$collectionFieldName] = array(
            'listFetcher' => $listFetcherInstance,
            'foreignKey' => $foreignKey,
        );
        $this->additionalData[$collectionFieldName] = array();
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

    /**
     * Loads lists of dependant tables from the database.
     *
     * @see addForeignListFetcher()
     */
    public function loadDependantTables()
    {
        foreach ($this->foreignListFetchers as $collectionFieldName => &$listFetcherDef) {
            /** @var $listFetcher ListFetcher */
            $listFetcher = $listFetcherDef['listFetcher'];
            /** @var $foreignKey ForeignKey */
            $foreignKey = $listFetcherDef['foreignKey'];
            $filter = array();
            foreach ($foreignKey->getReferences() as $column => $foreignColumn) {
                $filter[$column] = $this->$foreignColumn;
            }
            $this->additionalData[$collectionFieldName] = $listFetcher->getList($filter);
        }
    }

    /**
     * Saves lists of dependant tables to the database.
     *
     * @note New entries are inserted, existing ones are updated if present in lists, removed otherwise.
     * @note This method doesn't create a transaction, but it updates multiple items in multiple tables.
     * @note If a dependant table hasn't passed validation, hasValidationErrors() will return true.
     *
     * @see addForeignListFetcher()
     * @see hasValidationErrors()
     * @see startTransaction()
     */
    public function saveDependantTables()
    {
        /// Iterate through all dependant tables
        foreach ($this->foreignListFetchers as $collectionFieldName => &$listFetcherDef) {
            /** @var $foreignKey ForeignKey */
            $foreignKey = $listFetcherDef['foreignKey'];
            /// List of primary key values of the dependant table referencing data in this table gateway
            $dependantKeyList = array();
            /// Foreign key values of the dependant table referencing data in this table gateway
            $foreignKeyValues = array();
            /// List of primary key columns of the dependant table
            $columns = null;
            /// Table name of the dependant table
            $tableName = null;

            /// Iterate through th list, fill the local variables
            foreach ($this->additionalData[$collectionFieldName] as $tableGateway) {
                /** @var $tableGateway TableGateway */

                /// Save the dependant table gateway, perform INSERT or UPDATE
                $tableGateway->save();

                /// The following data is filled only on first iteration
                if ($columns === null) {
                    $columns = $tableGateway->getPrimaryKeyColumns();
                    /// If there is only one key column, remove the unnecessary array
                    if (count($columns) === 1) {
                        $columns = $columns[0];
                    }
                    $tableName = $tableGateway->getTable()->getName();
                    foreach ($foreignKey->getColumns() as $column) {
                        $foreignKeyValues[$column] = $tableGateway->$column;
                    }
                }

                /// The following data is filled by every iteration
                $values = array_values($tableGateway->getPrimaryKeyValues(false));
                /// If there is only one key column, remove the unnecessary array
                if (count($values) === 1) {
                    $values = $values[0];
                }
                $dependantKeyList[] = $values;
            }

            /// If data is found, DELETE all dependent table items which aren't in the list
            if ($columns !== null) {
                $delete = new Delete($tableName);
                foreach ($foreignKeyValues as $column => $value) {
                    $delete->addCondition($column, $value);
                }
                $delete->addInCondition($columns, $dependantKeyList, null, true);
                BaseHandler::getInstance()->query($delete->getSQL());
            }
        }
    }

    public function addDependantItem($fieldName, TableGateway $item)
    {
        foreach ($this->foreignListFetchers as $collectionFieldName => &$listFetcherDef) {
            if ($collectionFieldName !== $fieldName) {
                continue;
            }

            /** @var $listFetcher ListFetcher */
            $listFetcher = $listFetcherDef['listFetcher'];
            $tableGateway = $listFetcher->getTableGateway();
            if (!($item instanceof $tableGateway)) {
                throw new Exception('Item must be an instance of ' . get_class($tableGateway));
            }

            /** @var $foreignKey ForeignKey */
            $foreignKey = $listFetcherDef['foreignKey'];
            foreach ($foreignKey->getReferences() as $column => $foreignColumn) {
                $item->$foreignColumn = $this->$column;
            }

            $this->additionalData[$fieldName][] = $item;
            break;
        }
    }

    /**
     * Load the table gateway with values from the array.
     *
     * @param array $values - array of values to load into the table gateway
     * @param bool $isNew - true: just INSERT data into new object; false: mimic load(), UPDATE existing data
     * @see load()
     * @see loadBy()
     */
    public function loadWithArray(array $values, $isNew = false)
    {
        /// If updating existing data, trigger beforeLoad()
        if (!$isNew) {
            $this->beforeLoad();
        }

        /// Set values through the standard setter
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }

        /// If updating existing data, update oldValues as well so it seems the data has been read from the database
        if (!$isNew) {
            $this->oldValues = $this->recordData;
        }

        /// Set the appropriate flag for recordSetIsNew
        $this->recordSetIsNew = $isNew;

        /// Load dependant tables
        if ($this->useForeignListFetchers === true) {
            $this->loadDependantTables();
        }

        /// If updating existing data, trigger afterLoad()
        if (!$isNew) {
            $this->afterLoad();
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

        try {
            $this->loadBy(array($primaryKey[0] => $primaryKeyValue));
        } catch (NotFoundException $e) {
            throw $e;
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
            /** @var $column Column */
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
            throw new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql));
        }

        $row = BaseHandler::getInstance()->fetchAssoc($result);
        if ($row === false) {
            throw new NotFoundException('No records found for query: ' . $sql);
        }

        foreach ($row as $key => $value) {
            $this->recordData[$key] = Table::castValueToType($value, $this->table->getColumn($key)->getType());
        }
        $this->oldValues = $this->recordData;

        $this->recordSetIsNew = false;

        if ($this->useForeignListFetchers === true) {
            $this->loadDependantTables();
        }

        $this->afterLoad();
    }

    /**
     * Starts a new transaction.
     *
     * @note Only one transaction can be started per connection. Attempting to start another one will fail.
     * @note A transaction will be started only if the table gateway is using foreign list fetchers.
     *
     * @see commit()
     * @see rollback()
     * @see addForeignListFetcher()
     * @see useForeignListFetchers
     */
    protected function startTransaction()
    {
        if (!$this->isTransactionStarted && $this->useForeignListFetchers) {
            $this->isTransactionStarted = BaseHandler::getInstance()->startTransaction();
        }
    }

    /**
     * Commit the transaction started by this table gateway.
     *
     * @see startTransaction()
     * @see rollback()
     */
    protected function commit()
    {
        if ($this->isTransactionStarted === true) {
            BaseHandler::getInstance()->commit();
            $this->isTransactionStarted = false;
        }
    }

    /**
     * Rollback the transaction started by this table gateway.
     *
     * @see startTransaction()
     * @see commit()
     */
    protected function rollback()
    {
        if ($this->isTransactionStarted === true) {
            BaseHandler::getInstance()->rollback();
            $this->isTransactionStarted = false;
        }
    }

    public function save()
    {
        $this->beforeSave();

        $this->validateData();
        $this->validateDataUsingTableDefinition();
        if ($this->hasValidationErrors()) {
            return;
        }

        $this->startTransaction();

        if ($this->recordSetIsNew === true) {
            try {
                $this->saveNew();
            } catch (Exception $e) {
                $this->rollback();
                throw new DBException('Error while trying to insert new data in the database.', $e);
            }
        } else {
            try {
                $this->saveExisting();
            } catch (Exception $e) {
                $this->rollback();
                throw new DBException('Error while trying to update data in the database.', $e);
            }
        }

        if ($this->useForeignListFetchers === true) {
            $this->saveDependantTables();
        }

        if ($this->hasValidationErrors()) {
            $this->rollback();
            return;
        }

        $this->commit();

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
            /** @var $column Column */
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
            throw new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql));
        }

        if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
            throw new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql));
        }

        if (!BaseHandler::getInstance()->returningClauseIsSupported()) {
            $this->recordData[$primaryKeyColumns[0]] = BaseHandler::getInstance()->getLastInsertedRowID();
        } else {
            $row = BaseHandler::getInstance()->fetchAssoc($result);
            if ($row === false) {
                throw new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql));
            }

            foreach ($row as $key => $value) {
                $this->recordData[$key] = Table::castValueToType($value, $this->table->getColumn($key)->getType());
            }
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
            $update->addCondition($column, $this->oldValues[$column]);
        }
        $update->appendSemicolon();

        $sql = $update->getSQL();
        unset($update);

        $result = BaseHandler::getInstance()->query($sql);
        if ($result === false) {
            throw new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql));
        }

        if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
            throw new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql));
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
                throw new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql));
            }

            if (BaseHandler::getInstance()->getAffectedRows($result) === 0) {
                throw new DBException(BaseHandler::getInstance()->getLastError(), new DBException($sql));
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
            /// If the offset is a foreign key in dependant items, update them as well
            foreach ($this->foreignListFetchers as $collectionFieldName => &$listFetcherDef) {
                /** @var $foreignKey ForeignKey */
                $foreignKey = &$listFetcherDef['foreignKey'];
                foreach ($foreignKey->getReferences() as $column => $foreignColumn) {
                    if ($foreignColumn === $offset) {
                        foreach ($this->additionalData[$collectionFieldName] as &$tableGateway) {
                            /** @var $tableGateway TableGateway */
                            $tableGateway->$column = $value;
                        }
                        break;
                    }
                }
            }
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
                if (!$column->isNullable() && !$column->hasAutoIncrement()) {
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
                        $precision = $column->getPrecision();
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
        if (!empty($this->validationErrors)) {
            return true;
        }

        if ($this->useForeignListFetchers === true) {
            foreach ($this->foreignListFetchers as $collectionFieldName => $listFetcherDef) {
                foreach ($this->additionalData[$collectionFieldName] as $tableGateway) {
                    /** @var $tableGateway TableGateway */
                    if ($tableGateway->hasValidationErrors()) {
                        return true;
                    }
                }
            }
        }

        return false;
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
