<?php

namespace WebFW\Core\Database;

use WebFW\Core\Exceptions\DBException;
use WebFW\Database\BaseHandler;

class MySQLHandler extends BaseHandler
{
    protected $conntectionResource = false;
    protected $lastQueryResource = false;

    const DEFAULT_PORT = 3306;

    protected function __construct($username, $password, $dbName, $host, $port)
    {
        $this->connectionResource = new mysqli($host, $username, $password, $dbName, $port);

        if ($this->connectionResource->connect_error) {
            $connectError = $this->connectionResource->connect_error;
            $this->connectionResource = false;
            throw new DBException('Cannot connect to database.', new DBException($connectError));
        }
    }

    public function escapeIdentifier($identifier)
    {
        return $this->connectionResource->real_escape_string($identifier);
    }

    public function escapeLiteral($literal)
    {
        return $this->connectionResource->real_escape_string($literal);
    }

    public function query($query)
    {
        parent::query($query);
        $this->lastQueryResource = $this->connectionResource->query($query);
        return $this->lastQueryResource;
    }

    public function getLastError()
    {
        return $this->lastQueryResource->error;
    }

    public function fetchAssoc($queryResource = false, $row = null)
    {
        $resource = &$this->lastQueryResource;
        if ($queryResource !== false) {
            $resource = &$queryResource;
        }

        if (!($resource instanceof mysqli_result)) {
            return null;
        }

        $returnToRow = null;
        if ($row !== null) {
            $returnToRow = $resource->current_field;
            $ok = $resource->data_seek($row);
            if (!$ok) {
                $resource->data_seek($returnToRow);
                return null;
            }
        }

        $data = $resource->fetch_assoc();

        if ($returnToRow !== null) {
            $resource->data_seek($returnToRow);
        }

        return $data;
    }

    public function fetchAll($queryResource = false)
    {
        $resource = &$this->lastQueryResource;
        if ($queryResource !== false) {
            $resource = &$queryResource;
        }

        if (!($resource instanceof mysqli_result)) {
            return null;
        }

        return $resource->fetch_all(MYSQLI_ASSOC);
    }

    public function getAffectedRows($queryResource = false)
    {
        $resource = &$this->lastQueryResource;
        if ($queryResource !== false) {
            $resource = &$queryResource;
        }

        if ($resource instanceof mysqli_result) {
            return $resource->num_rows;
        }

        return $this->connectionResource->affected_rows;
    }

    public function getLimitAndOffset($limit, $offset = 0)
    {
        if ($offset > 0) {
            $limit = $offset . ', ' . $limit;
        }

        return 'LIMIT ' . $limit;
    }

    public function convertBoolean($value)
    {
        $value = (int) $value;
        switch($value) {
            case 1:
                return true;
            case 0:
                return false;
            default:
                return null;
        }
    }

    public function returningClauseIsSupported()
    {
        return false;
    }
}
