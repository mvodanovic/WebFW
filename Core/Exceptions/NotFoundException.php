<?php

namespace WebFW\Framework\Core\Exceptions;

use WebFW\Framework\Core\Exception;

/**
 * Class NotFoundException
 *
 * Exception intended to be thrown when the requested resource cannot be found.
 * This exception has it's code automatically set to 404.
 *
 * @package WebFW\Framework\Core\Exceptions
 * @see Exception
 */
class NotFoundException extends Exception
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
        parent::__construct($message, 404, $e, $displayErrorMessage);
    }
}
