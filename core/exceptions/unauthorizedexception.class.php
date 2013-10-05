<?php

namespace WebFW\Core\Exceptions;

use WebFW\Core\Exception;

class UnauthorizedException extends Exception
{
    public function __construct($message, \Exception $e = null)
    {
        parent::__construct($message, 401, $e);
    }
}
