<?php

namespace WebFW\Database;

use WebFW\Core\Exception;
use Config\Specifics\Data;

abstract class BaseHandler
{
    protected static $instances = array();
    protected static $activeInstanceID = null;

    /**
     * Default port of the DBMS. Should be overridden in implementing classes.
     */
    const DEFAULT_PORT = null;

    protected $isTransactionStarted = false;

    /**
     * Get the instance of the implementing class. Create one if it doesn't exist yet.
     *
     * @note If the $instanceID parameter is set, an instance with that ID must exist or an exception will be thrown.
     * @note If the $instanceID parameter not is set and no instances exist, a new instance will be created from config params.
     *
     * @param int $instanceID - ID of DB instance to fetch, NULL for last active instance
     * @return BaseHandler - instance of the implementing class
     * @throws \WebFW\Core\Exception - if invalid instance ID given or couldn't create a new instance
     */
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
            if ($dbName !== null) {
                /** @var $handler BaseHandler */
                $handler = Data::GetItem('DB_HANDLER');
                if (class_exists($handler) && is_subclass_of($handler, '\\WebFW\\Database\\BaseHandler')) {
                    $instanceID = $handler::createNewConnection($dbUsername, $dbPassword, $dbName, $dbHost, $dbPort);
                } else {
                    throw new Exception('DB handler \'' . $handler . '\' does not exist.');
                }
            }
        }

        if ($instanceID === null) {
            throw new Exception('Couldn\'t create a DB handler instance');
        }

        if (!array_key_exists($instanceID, static::$instances)) {
            throw new Exception('Invalid database connection instance ID given!');
        }

        return static::$instances[$instanceID];
    }

    /**
     * Set the instance with the given ID as active.
     *
     * @param int $instanceID - the instance ID
     * @throws \WebFW\Core\Exception - if invalid instance ID given
     */
    public static function setActiveInstanceID($instanceID)
    {
        if (!array_key_exists($instanceID, static::$instances)) {
            throw new Exception('Invalid database connection instance ID given!');
        }

        static::$activeInstanceID = $instanceID;
    }

    /**
     * Get the currently active instance ID.
     *
     * @return null|int - the currently active instance ID, NULL if no instance created yet
     */
    public static function getActiveInstanceID()
    {
        return static::$activeInstanceID;
    }

    /**
     * Create a new database connection.
     *
     * @param string $username - username for connecting to the database
     * @param string $password - username for connecting to the database
     * @param string $dbName - name of the database to connect to
     * @param string $host - host name or IP address, defaults to "127.0.0.1"
     * @param int $port - port on which the DBMS is listening, defaults to the default port of the DBMS in question
     * @return int - ID of the newly created instance
     */
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

    /**
     * Preform a query on the database.
     *
     * @param string $query - the query to be sent to the database
     * @return null - overridden in implementing classes
     */
    public function query($query)
    {
        if (array_key_exists('db_debug', $_REQUEST) && $_REQUEST['db_debug'] == 1) {
            trigger_error('Query: ' . $query);
        }
        return null;
    }

    /**
     * Start a transaction on the database.
     *
     * @note Only one transaction can be started at a time. Other attempts will fail until the transaction is complete.
     *
     * @return bool - true if the transaction was successfully started, false otherwise
     * @see commit()
     * @see rollback()
     */
    public function startTransaction()
    {
        if ($this->isTransactionStarted === true) {
            return false;
        }

        $this->query('START TRANSACTION');
        $this->isTransactionStarted = true;

        return true;
    }

    /**
     * Commit a transaction on the database.
     *
     * @see startTransaction()
     * @see rollback()
     */
    public function commit()
    {
        if ($this->isTransactionStarted === true) {
            $this->query('COMMIT');
            $this->isTransactionStarted = false;
        }
    }

    /**
     * Rollback a transaction on the database.
     *
     * @see startTransaction()
     * @see commit()
     */
    public function rollback()
    {
        if ($this->isTransactionStarted === true) {
            $this->query('ROLLBACK');
            $this->isTransactionStarted = false;
        }
    }

    /**
     * A quick function for escaping values to be sent to the database.
     *
     * @param mixed $literal - the literal to be sent
     * @return mixed - the escaped literal
     */
    public static function e($literal)
    {
        return static::getInstance()->escapeLiteral($literal);
    }

    /**
     * A quick function for preforming a query and fetching all the results.
     *
     * @param string $query - the query to be sent to the database
     * @return mixed - the result; differs according to the query type & DBMS
     */
    public static function q($query)
    {
        $resource = static::getInstance()->query($query);
        return static::getInstance()->fetchAll($resource);
    }

    abstract public function getLastError();
    abstract public function escapeIdentifier($identifier);
    abstract public function escapeLiteral($literal);
    abstract public function fetchAssoc($queryResource = false, $row = null);
    abstract public function fetchAll($queryResource = false);
    abstract public function getAffectedRows($queryResource = false);
    abstract public function getLimitAndOffset($limit, $offset = 0);
    abstract public function convertBoolean($value);
    abstract public function returningClauseIsSupported();
}
