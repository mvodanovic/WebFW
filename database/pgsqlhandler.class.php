<?php

namespace WebFW\Database;

use WebFW\Core\Exception;

class PgSQLHandler extends BaseHandler
{
    protected $connectionResource = false;
    protected $connectionString = '';
    protected $lastQueryResource = false;

    const DEFAULT_PORT = 5432;

    protected function __construct($username, $password, $dbName, $host, $port)
    {
        $this->addToConnectionString('user', $username);
        $this->addToConnectionString('password', $password);
        $this->addToConnectionString('dbname', $dbName);
        $this->addToConnectionString('port', $port);

        /// check if the passed host is an IP address or a host name
        if (inet_pton($host) !== false) {
            /// IP address
            $this->addToConnectionString('hostaddr', $host);
        } else {
            /// host name
            $this->addToConnectionString('host', $host);
        }

        $this->connectionResource = pg_connect($this->connectionString);

        if ($this->connectionResource === false) {
            throw new Exception('Cannot connect to database!');
        }
    }

    protected function addToConnectionString($key, $value)
    {
        if ($this->connectionString !== '') {
            $this->connectionString .= ' ';
        }

        $this->connectionString .= $key . '=' . addcslashes($value, "\\'");
    }

    public function escapeIdentifier($identifier)
    {
        return pg_escape_identifier($this->connectionResource, $identifier);
    }

    public function escapeLiteral($literal)
    {
        if ($literal === null) {
            return 'NULL';
        } elseif ($literal === true) {
            return 'TRUE';
        } elseif ($literal === false) {
            return 'FALSE';
        } elseif (is_int($literal) || is_float($literal)) {
            return $literal;
        } else {
            return pg_escape_literal($this->connectionResource, $literal);
        }
    }

    public function query($query)
    {
        parent::query($query);
        $this->lastQueryResource = pg_query($this->connectionResource, $query);
        return $this->lastQueryResource;
    }

    public function fetchAssoc($queryResource = false, $row = null)
    {
        if ($queryResource === false) {
            $queryResource = $this->lastQueryResource;
        }

        return pg_fetch_assoc($queryResource, $row);
    }

    public function fetchAll($queryResource = false)
    {
        if ($queryResource === false) {
            $queryResource = $this->lastQueryResource;
        }

        $result = pg_fetch_all($queryResource);
        if ($result === false) {
            $result = array();
        }

        return $result;
    }

    public function getAffectedRows($queryResource = false)
    {
        if ($queryResource === false) {
            $queryResource = $this->lastQueryResource;
        }

        return pg_affected_rows($queryResource);
    }

    public function getLimitAndOffset($limit, $offset = 0)
    {
        if ($limit <= 0) {
            return '';
        }

        $return = 'LIMIT ' . $limit;

        if ($offset <= 0) {
            return $return;
        }

        $return .= ' OFFSET ' . $offset;

        return $return;
    }

    public function convertBoolean($value)
    {
        switch($value) {
            case 't':
            case 1:
                return true;
            case 'f':
            case 0:
                return false;
            default:
                return null;
        }
    }

    public function returningClauseIsSupported()
    {
        return true;
    }
}
