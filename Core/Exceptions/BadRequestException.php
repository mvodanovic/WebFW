<?php

namespace WebFW\Framework\Core\Exceptions;

use WebFW\Framework\Core\Exception;

/**
 * Class BadRequestException
 *
 * Exception intended to be thrown when a bad request has been made.
 * This exception has it's code automatically set to 400.
 *
 * @package WebFW\Framework\Core\Exceptions
 * @see Exception
 */
class BadRequestException extends Exception
{
    /**
     * Constructor.
     *
     * @param string|null $message
     * @param \Exception|null $e
     * @param bool $displayResponseBody
     * @see Exception::__construct()
     */
    public function __construct($message = null, \Exception $e = null, $displayResponseBody = true)
    {
        parent::__construct($message, 400, $e, $displayResponseBody);
    }
}
