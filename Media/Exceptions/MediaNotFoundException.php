<?php

namespace mvodanovic\WebFW\Media\Exceptions;

use mvodanovic\WebFW\Core\Exceptions\NotFoundException;

class MediaNotFoundException extends NotFoundException
{
    public function __construct($message, \Exception $e = null, $displayErrorMessage = true)
    {
        parent::__construct($message, $e, $displayErrorMessage);
    }
}
