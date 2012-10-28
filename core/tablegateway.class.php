<?php
namespace WebFW\Core;

use \WebFW\Core\Database\BaseHandler;

abstract class TableGateway
{
	protected $tableDefinition = null;
	protected $recordData = array();
	protected $oldValues = array();

	protected $escapedTableName = null;
	protected $escapedPrimaryKeyName = null;
	protected $escapedFieldNames = null;
	protected $recordSetIsNew = true;

	const SQL_SELECT = 'SELECT ::field_names:: FROM ::table_name:: WHERE ::field_pairs:: LIMIT 1;';
	const SQL_INSERT = 'INSERT INTO ::table_name:: (::field_names::) VALUES (::field_values::) RETURNING ::primary_key_name::;';
	const SQL_UPDATE = 'UPDATE ::table_name:: SET ::field_pairs:: WHERE ::primary_key_name:: = ::primary_key_value::;';
	const SQL_DELETE = 'DELETE FROM ::table_name:: WHERE ::primary_key_name:: = ::primary_key_value::;';

	public function __construct()
	{
		if ($this->tableDefinition === null) {
			throw new Exception('Table definition not set!');
		}

		foreach ($this->tableDefinition->getFields() as $key => $value) {
			$this->recordData[$key] = $value;
			$this->oldValues[$key] = $value;
		}
	}

	protected function setTableDefinition($definition)
	{
		$definition = '\\Application\\DBLayer\\Definitions\\' . $definition;
		$this->tableDefinition = new $definition;
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
			$this->recordData[$key] = TableDefinition::castValueToType($value, $this->tableDefinition->getFieldType($key));
		}
	}

	public function load($primaryKeyValue)
	{
		$this->loadBy(array($this->tableDefinition->getKeyfield() => $primaryKeyValue));
	}

	public function loadBy(array $unique)
	{
		$this->beforeLoad();

		$sql = static::SQL_SELECT;

		$tableName = $this->getEscapedTableName();
		$fieldNames = implode(',', $this->getEscapedFieldNames(true));
		$fieldPairs = array();
		foreach ($unique as $key => $value) {
			$fieldPairs[] = $this->escapeTableIdentifier($key). ' = ' . $this->escapeTableLiteral($value);
		}
		if (count($fieldPairs) === 0) {
			return;
		}
		$fieldPairs = implode(' AND ', $fieldPairs);

		$sql = preg_replace('#::table_name::#', $tableName, $sql, 1);
		$sql = preg_replace('#::field_names::#', $fieldNames, $sql, 1);
		$sql = preg_replace('#::field_pairs::#', $fieldPairs, $sql, 1);

		$result = BaseHandler::getInstance()->query($sql);
		if ($result === false) {
			throw new Exception('Database select failed!');
		}

		$row = BaseHandler::getInstance()->fetchAssoc($result);
		if ($row === false) {
			throw new Exception('Database select failed!');
		}

		foreach ($row as $key => $value) {
			$this->recordData[$key] = TableDefinition::castValueToType($value, $this->tableDefinition->getFieldType($key));
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

		$sql = static::SQL_INSERT;

		$tableName = $this->getEscapedTableName();
		$fieldNames = implode(',', $this->getEscapedFieldNames());
		$fieldValues = array();
		foreach ($this->recordData as $key => $value) {
			if ($key === $this->tableDefinition->getKeyfield()) {
				continue;
			}
			$fieldValues[] = $this->escapeTableLiteral($value);
		}
		$fieldValues = implode(',', $fieldValues);
		$primaryKeyName = $this->getEscapedPrimaryKeyName();

		$sql = preg_replace('#::table_name::#', $tableName, $sql, 1);
		$sql = preg_replace('#::field_names::#', $fieldNames, $sql, 1);
		$sql = preg_replace('#::field_values::#', $fieldValues, $sql, 1);
		$sql = preg_replace('#::primary_key_name::#', $primaryKeyName, $sql, 1);

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
			$this->recordData[$key] = TableDefinition::castValueToType($value, $this->tableDefinition->getFieldType($key));
		}

		$this->oldValues = $this->recordData;

		$this->recordSetIsNew = false;

		$this->afterSaveNew();
	}

	public function saveExisting()
	{
		$this->beforeSaveExisting();

		$sql = static::SQL_UPDATE;

		$fieldPairs = array();
		foreach ($this->recordData as $key => $value) {
			if ($value === $this->oldValues[$key]) {
				continue;
			}
			$fieldPairs[] = $this->escapeTableIdentifier($key). ' = ' . $this->escapeTableLiteral($value);
		}
		if (count($fieldPairs) === 0) {
			return;
		}
		$fieldPairs = implode(',', $fieldPairs);

		$tableName = $this->getEscapedTableName();
		$primaryKeyName = $this->getEscapedPrimaryKeyName();
		$primaryKeyValue = $this->escapeTableLiteral($this->recordData[$this->tableDefinition->getKeyfield()]);

		$sql = preg_replace('#::field_pairs::#', $fieldPairs, $sql, 1);
		$sql = preg_replace('#::table_name::#', $tableName, $sql, 1);
		$sql = preg_replace('#::primary_key_name::#', $primaryKeyName, $sql, 1);
		$sql = preg_replace('#::primary_key_value::#', $primaryKeyValue, $sql, 1);

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

		$sql = static::SQL_DELETE;

		$tableName = $this->getEscapedTableName();
		$primaryKeyName = $this->getEscapedPrimaryKeyName();
		$primaryKeyValue = $this->escapeTableLiteral($this->recordData[$this->tableDefinition->getKeyfield()]);

		$sql = preg_replace('#::table_name::#', $tableName, $sql, 1);
		$sql = preg_replace('#::primary_key_name::#', $primaryKeyName, $sql, 1);
		$sql = preg_replace('#::primary_key_value::#', $primaryKeyValue, $sql, 1);

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

	protected function escapeTableIdentifier($identifier)
	{
		return BaseHandler::getInstance()->escapeIdentifier($identifier);
	}

	protected function escapeTableLiteral($literal)
	{
		return BaseHandler::getInstance()->escapeLiteral($literal);
	}

	protected function getEscapedTableName()
	{
		if ($this->escapedTableName === null) {
			$this->escapedTableName = $this->escapeTableIdentifier($this->tableDefinition->getTableName());
		}

		return $this->escapedTableName;
	}

	protected function getEscapedPrimaryKeyName()
	{
		if ($this->escapedPrimaryKeyName === null) {
			$this->escapedPrimaryKeyName = $this->escapeTableIdentifier($this->tableDefinition->getKeyfield());
		}

		return $this->escapedPrimaryKeyName;
	}

	protected function getEscapedFieldNames($withPrimaryKeyField = false)
	{
		if ($this->escapedFieldNames === null) {
			$this->escapedFieldNames = array();
			foreach ($this->recordData as $name => $value) {
				if ($withPrimaryKeyField === false && $name === $this->tableDefinition->getKeyfield()) {
					continue;
				}
				$this->escapedFieldNames[] = $this->escapeTableIdentifier($name);
			}
		}

		return $this->escapedFieldNames;
	}

	public function getValues()
	{
		return $this->recordData;
	}
}
