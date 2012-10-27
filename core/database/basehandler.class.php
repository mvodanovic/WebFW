<?php
namespace WebFW\Core\Database;

use \WebFW\Core\Exception;

abstract class BaseHandler
{
	protected static $instances = array();
	protected static $activeInstanceID = null;

	public static function getInstance($instanceID = null)
	{
		if ($instanceID === null) {
			$instanceID = static::$activeInstanceID;
		}

		if ($instanceID === null) {
			return null;
		}

		if (!array_key_exists($instanceID, static::$instances)) {
			throw new Exception('Invalid database connection instance ID given!');
		}

		return static::$instances[$instanceID];
	}

	public static function setActiveInstanceID($instanceID)
	{
		if (!array_key_exists($instanceID, static::$instances)) {
			throw new Exception('Invalid database connection instance ID given!');
		}

		static::$activeInstanceID = $instanceID;
	}

	public static function getActiveInstanceID()
	{
		return static::$activeInstanceID;
	}

	public static function createNewConnection($username, $password, $dbName, $host = '127.0.0.1', $port = 5432)
	{
		$instance = new static($username, $password, $dbName, $host = '127.0.0.1', $port = 5432);
		static::$instances[] = &$instance;
		end(static::$instances);
		$instanceID = key(static::$instances);
		reset(static::$instances);

		if (static::$activeInstanceID === null) {
			static::$activeInstanceID = $instanceID;
		}

		return $instanceID;
	}

	public function query($query)
	{
		if (array_key_exists('db_debug', $_REQUEST) && $_REQUEST['db_debug'] == 1) {
			trigger_error('Query: ' . $query);
		}
	}

	public static function e($literal)
	{
		return static::getInstance()->escapeLiteral($literal);
	}

	public static function q($query)
	{
		$resource = static::getInstance()->query($query);
		return static::getInstance()->fetchAll($resource);
	}
}