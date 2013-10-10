<?php

namespace WebFW\Core\Exceptions;

use WebFW\Core\Exception;

/**
 * Class ForbiddenException
 *
 * Exception intended to be thrown when the user tries to access a resource he doesn't have sufficient privileges for.
 * This exception has it's code automatically set to 403.
 *
 * @package WebFW\Core\Exceptions
 * @see Exception
 */
class ForbiddenException extends Exception
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
        parent::__construct($message, 403, $e, $displayErrorMessage);
    }
}
