<?php

namespace WebFW\Framework\Dev;

use WebFW\Framework\Core\HTMLController;
use WebFW\Framework\Dev\Classes\DevHelper;

/**
 * Class Controller
 *
 * A basic controller used to display developer options.
 *
 * @package WebFW\Framework\Dev
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
