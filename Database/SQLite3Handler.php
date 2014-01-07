<?php

namespace WebFW\Framework\Database;

use WebFW\Framework\Core\Exceptions\DBException;
use SQLite3;
use SQLite3Result;

class SQLite3Handler extends BaseHandler
{
    /**
     * @var SQLite3
     */
    protected $connectionResource = false;

    /**
     * @var SQLite3Result
     */
    protected $lastQueryResource = false;

    protected function __construct($unused1, $unused2, $filename, $unused3, $unused4)
    {
        try {
            $this->connectionResource = new SQLite3($filename);
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
            return "'" . $this->connectionResource->escapeString($literal) . "'";
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
        if (strtoupper(substr($query, 0, 6)) === 'SELECT') {
            $this->lastQueryResource = $this->connectionResource->query($query);
        } else {
            $this->lastQueryResource = $this->connectionResource->exec($query);
        }
        parent::completeQuery();
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
            return false;
        }

        $data = $resource->fetchArray(SQLITE3_ASSOC);
        if ($data === false) {
            $resource->finalize();
            return false;
        }

        return $data;
    }

    public function fetchAll($queryResource = false)
    {
        $resultSet = array();
        while (($row = $this->fetchAssoc($queryResource)) !== false) {
            $resultSet[] = $row;
        }

        return $resultSet;
    }

    public function getAffectedRows($queryResource = false)
    {
        $resource = $this->lastQueryResource;
        if ($queryResource instanceof \SQLite3Result || $queryResource === true) {
            $resource = $queryResource;
        }

        if ($resource instanceof \SQLite3Result) {
            $count = 0;
            while ($resource->fetchArray(SQLITE3_ASSOC) !== false) {
                $count++;
            }
            $resource->reset();
            return $count;
        } elseif ($resource === true) {
            return $this->connectionResource->changes();
        } else {
            return 0;
        }
    }

    public function getLastInsertedRowID()
    {
        return $this->connectionResource->lastInsertRowID();
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
