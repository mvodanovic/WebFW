<?php

namespace mvodanovic\WebFW\Dev;

use mvodanovic\WebFW\Core\HTMLController;
use mvodanovic\WebFW\Dev\Classes\DevHelper;

/**
 * Class Controller
 *
 * A basic controller used to display developer options.
 *
 * @package mvodanovic\WebFW
 */
abstract class Controller extends HTMLController
{
    /**
     * The realm message which will be displayed to users for authentication
     */
    const REALM_MESSAGE = 'Developer area';

    protected function __construct()
    {
        DevHelper::requestAuthentication(static::REALM_MESSAGE);
        parent::__construct();
    }
}
