<?php
namespace WebFW\Core;

use \WebFW\Core\Database\BaseHandler;

abstract class TableDefinition
{
	protected $tableName = null;
	protected $keyfield = null;
	protected $fields = array();
	protected $fieldType = array();

	const CONSTRAINT_PRIMARY_KEY = 1;

	const FIELD_TYPE_STRING = 1;
	const FIELD_TYPE_INTEGER = 2;
	const FIELD_TYPE_BOOLEAN = 3;
	const FIELD_TYPE_FLOAT = 4;

	protected function setTableName($name)
	{
		$this->tableName = $name;
	}

	protected function addField($fieldName, $defaultValue = null, $fieldType = self::FIELD_TYPE_STRING, $constraint = null)
	{
		$this->fields[$fieldName] = static::castValueToType($defaultValue, $fieldType);
		$this->fieldType[$fieldName] = $fieldType;

		if ($constraint === static::CONSTRAINT_PRIMARY_KEY) {
			$this->keyfield = $fieldName;
		}
	}

	public function getTableName()
	{
		return $this->tableName;
	}

	public function getKeyfield()
	{
		return $this->keyfield;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getFieldType($fieldName)
	{
		if (!array_key_exists($fieldName, $this->fieldType)) {
			return null;
		}

		return $this->fieldType[$fieldName];
	}

	public static function castValueToType($value, $type)
	{
		if ($value === null) {
			return null;
		}

		switch ($type) {
			case static::FIELD_TYPE_STRING:
				return (string) $value;
			case static::FIELD_TYPE_INTEGER:
				return (int) $value;
			case static::FIELD_TYPE_BOOLEAN:
				return (bool) BaseHandler::getInstance()->convertBoolean($value);
			case static::FIELD_TYPE_FLOAT:
				return (float) $value;
			default:
				return $value;
		}
	}
}
