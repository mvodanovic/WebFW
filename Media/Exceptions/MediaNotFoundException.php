<?php

namespace WebFW\Framework\Media\Exceptions;

use WebFW\Framework\Core\Exceptions\NotFoundException;

class MediaNotFoundException extends NotFoundException
{
    public function __construct($message, \Exception $e = null, $displayErrorMessage = true)
    {
        parent::__construct($message, $e, $displayErrorMessage);
    }
}
