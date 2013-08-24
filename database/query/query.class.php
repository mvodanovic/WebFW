<?php

namespace WebFW\Database\Query;

use WebFW\Database\BaseHandler;

abstract class Query
{
    protected $tableName;
    protected $appendSemicolon = false;

    public function __construct($tableName)
    {
        $this->tableName = $this->escapeIdentifier($tableName);
    }

    public function appendSemicolon()
    {
        $this->appendSemicolon = true;
    }

    protected function escapeIdentifier($identifier)
    {
        return BaseHandler::getInstance()->escapeIdentifier($identifier);
    }

    protected function escapeLiteral($literal)
    {
        return BaseHandler::getInstance()->escapeLiteral($literal);
    }

    abstract public function getSQL();
}
