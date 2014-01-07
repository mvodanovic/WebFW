<?php

namespace WebFW\Framework\Core\Exceptions;

use WebFW\Framework\Core\Exception;

/**
 * Class DBException
 *
 * Exception intended to be thrown when a database system reports a problem.
 * This exception has it's code automatically set to 500.
 *
 * @package WebFW\Framework\Core\Exceptions
 * @see Exception
 */
class DBException extends Exception
{
    /**
     * Constructor.
     *
     * @param string|null $message
     * @param \Exception|null $e
     * @param bool $displayErrorMessage
     * @see Exception::__construct()
     */
    public function __construct($message, \Exception $e = null, $displayErrorMessage = true)
    {
        parent::__construct($message, 500, $e, $displayErrorMessage);
    }
}
