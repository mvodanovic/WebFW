<?php

namespace WebFW\Framework\Core\Exceptions;

use WebFW\Framework\Core\Exception;

/**
 * Class UnauthorizedException
 *
 * Exception intended to be thrown when the user tries to access a resource without prior authorization.
 * This exception has it's code automatically set to 401.
 *
 * @package WebFW\Framework\Core\Exceptions
 * @see Exception
 */
class UnauthorizedException extends Exception
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
        parent::__construct($message, 401, $e, $displayErrorMessage);
    }
}
