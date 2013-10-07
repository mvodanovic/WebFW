<?php

namespace WebFW\Database;

use WebFW\Core\Exceptions\DBException;

class SQLite3Handler extends BaseHandler
{
    protected $conntectionResource = false;
    protected $lastQueryResource = false;

    protected function __construct($unused1, $unused2, $filename, $unused3, $unused4)
    {
        try {
            $this->connectionResource = new \SQLite3($filename);
        } catch (\Exception $e) {
            $this->connectionResource = false;
            throw new DBException('Cannot connect to database', $e);
        }
    }

    public function escapeIdentifier($identifier)
    {
        return '"' . $identifier . '"';
    }

    public function escapeLiteral($literal)
    {
	if (is_string($literal)) {
            return $this->connectionResource->escapeString($literal);
        } elseif ($literal === null) {
            return 'NULL';
        } elseif (is_bool($literal)) {
            return (int) $literal;
        } else {
            return $literal;
        }
    }

    public function query($query)
    {
        parent::query($query);
        $this->lastQueryResource = $this->connectionResource->query($query);
        return $this->lastQueryResource;
    }

    public function getLastError()
    {
        return $this->connectionResource->lastErrorMsg();
    }

    public function fetchAssoc($queryResource = false, $row = null)
    {
        $resource = &$this->lastQueryResource;
        if ($queryResource instanceof \SQLite3Result) {
            $resource = &$queryResource;
        }

        if (!($resource instanceof \SQLite3Result)) {
            return null;
        }

        $data = $resource->fetchArray(SQLITE3_ASSOC);
        if ($data === false) {
            $resource->finalize();
            return null;
        }

        return $data;
    }

    public function fetchAll($queryResource = false)
    {
        $resultSet = array();
        while (($row = $this->fetchAssoc($queryResource)) !== null) {
            $resultSet[] = $row;
        }

        return $resultSet;
    }

    public function getAffectedRows($queryResource = false)
    {
        $resource = &$this->lastQueryResource;
        if ($queryResource instanceof \SQLite3Result || $queryResource === true) {
            $resource = &$queryResource;
        }

        if ($resource instanceof \SQLite3Result) {
            $count = 0;
            while ($resource->fetchArray(SQLITE3_ASSOC) !== null) {
                $count++;
            }
            $resource->reset();
            return $count;
        } elseif ($resource === true) {
            return $this->connectionResource->changes();
        } else {
            return 0;
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
	if ($value === null) {
            return null;
        }

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
