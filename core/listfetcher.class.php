<?php
namespace WebFW\Core;

use \WebFW\Core\Database\BaseHandler;

abstract class ListFetcher
{
	protected $tableDefinition = null;
	protected $filter = array();
	protected $sort = array();
	protected $limit = 50;
	protected $offset = 0;

	protected $escapedTableName = null;
	protected $escapedFieldNames = null;

	const SQL_SELECT = 'SELECT ::field_names:: FROM ::table_name:: ::where_clause:: ::order_clause:: ::limit_and_offset::;';

	public function __construct()
	{
		if ($this->tableDefinition === null) {
			throw new Exception('Table definition not set!');
		}

		$this->sort[$this->tableDefinition->getKeyfield()] = 'DESC';
	}

	protected function setTableDefinition($definition)
	{
		$definition = '\\Application\\DBLayer\\Definitions\\' . $definition;
		$this->tableDefinition = new $definition;
	}

	public function getList($filter = null, $sort = null, $limit = null, $offset = null)
	{
		$sql = static::SQL_SELECT;

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
			$this->offset = null;
		}

		$tableName = $this->getEscapedTableName();
		$fieldNames = implode(',', $this->getEscapedFieldNames());
		$whereClause = array();
		foreach ($filter as $key => $value) {
			$whereClause[] = $this->escapeTableIdentifier($key) . ' = ' . $this->escapeTableLiteral($value);
		}
		$whereClause = implode(' AND ', $whereClause);
		if ($whereClause !== '') {
			$whereClause = 'WHERE ' . $whereClause;
		}
		$orderClause = array();
		foreach ($sort as $key => $value) {
			$orderClause[] = $this->escapeTableIdentifier($key) . ' ' . $value;
		}
		$orderClause = implode(', ', $orderClause);
		if ($orderClause !== '') {
			$orderClause = 'ORDER BY ' . $orderClause;
		}
		$limitAndOffset = $this->getLimitAndOffset($limit, $offset);

		$sql = preg_replace('#::table_name::#', $tableName, $sql, 1);
		$sql = preg_replace('#::field_names::#', $fieldNames, $sql, 1);
		$sql = preg_replace('#::where_clause::#', $whereClause, $sql, 1);
		$sql = preg_replace('#::order_clause::#', $orderClause, $sql, 1);
		$sql = preg_replace('#::limit_and_offset::#', $limitAndOffset, $sql, 1);

		$result = BaseHandler::getInstance()->query($sql);
		if ($result === false) {
			throw new Exception('Database query error!');
		}

		return BaseHandler::getInstance()->fetchAll($result);
	}

	public function getCount($filter = null)
	{
		$sql = static::SQL_SELECT;

		if ($filter === null) {
			$filter = $this->filter;
		}

		$tableName = $this->getEscapedTableName();
		$fieldNames = 'COUNT(*) AS "cnt"';
		$whereClause = array();
		foreach ($filter as $key => $value) {
			$whereClause[] = $this->escapeTableIdentifier($key). ' = ' . $this->escapeTableLiteral($value);
		}
		$whereClause = implode(' AND ', $whereClause);
		if ($whereClause !== '') {
			$whereClause = 'WHERE ' . $whereClause;
		}
		$orderClause = '';
		$limitAndOffset = '';

		$sql = preg_replace('#::table_name::#', $tableName, $sql, 1);
		$sql = preg_replace('#::field_names::#', $fieldNames, $sql, 1);
		$sql = preg_replace('#::where_clause::#', $whereClause, $sql, 1);
		$sql = preg_replace('#::order_clause::#', $orderClause, $sql, 1);
		$sql = preg_replace('#::limit_and_offset::#', $limitAndOffset, $sql, 1);

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

	protected function escapeTableIdentifier($identifier)
	{
		return BaseHandler::getInstance()->escapeIdentifier($identifier);
	}

	protected function escapeTableLiteral($literal)
	{
		return BaseHandler::getInstance()->escapeLiteral($literal);
	}

	protected function getLimitAndOffset($limit, $offset)
	{
		return BaseHandler::getInstance()->getLimitAndOffset($limit, $offset);
	}

	protected function getEscapedTableName()
	{
		if ($this->escapedTableName === null) {
			$this->escapedTableName = $this->escapeTableIdentifier($this->tableDefinition->getTableName());
		}

		return $this->escapedTableName;
	}

	protected function getEscapedFieldNames()
	{
		if ($this->escapedFieldNames === null) {
			$this->escapedFieldNames = array();
			foreach ($this->tableDefinition->getFields() as $name => $value) {
				$this->escapedFieldNames[] = $this->escapeTableIdentifier($name);
			}
		}

		return $this->escapedFieldNames;
	}
}