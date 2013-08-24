<?php

namespace WebFW\Database;

use WebFW\Core\Exception;
use Config\Specifics\Data;

abstract class BaseHandler
{
    protected static $instances = array();
    protected static $activeInstanceID = null;

    const DEFAULT_PORT = null;

    public static function getInstance($instanceID = null)
    {
        if ($instanceID === null) {
            $instanceID = static::$activeInstanceID;
        }

        if ($instanceID === null) {
            $dbUsername = Data::GetItem('DB_USERNAME');
            $dbPassword = Data::GetItem('DB_PASSWORD');
            $dbName = Data::GetItem('DB_NAME');
            $dbHost = Data::GetItem('DB_HOST');
            $dbPort = Data::GetItem('DB_PORT');
            if ($dbUsername !== null && $dbPassword !== null && $dbName !== null) {
                $handler = Data::GetItem('DB_HANDLER');
                if (class_exists($handler) && is_subclass_of($handler, '\\WebFW\\Database\\BaseHandler')) {
                    $instanceID = $handler::createNewConnection($dbUsername, $dbPassword, $dbName, $dbHost, $dbPort);
                } else {
                    throw new Exception('DB handler \'' . $handler . '\' does not exist.');
                }
            }
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

    public static function createNewConnection($username, $password, $dbName, $host = null, $port = null)
    {
        if ($port === null) {
            $port = static::DEFAULT_PORT;
        }

        if ($host === null) {
            $host = '127.0.0.1';
        }

        $instance = new static($username, $password, $dbName, $host, $port);
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

    abstract public function escapeIdentifier($identifier);
    abstract public function escapeLiteral($literal);
    abstract public function fetchAssoc($queryResource = false, $row = null);
    abstract public function fetchAll($queryResource = false);
    abstract public function getAffectedRows($queryResource = false);
    abstract public function getLimitAndOffset($limit, $offset = 0);
    abstract public function convertBoolean($value);
    abstract public function returningClauseIsSupported();
}
