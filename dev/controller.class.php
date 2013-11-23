<?php

namespace WebFW\Dev;

use WebFW\Core\HTMLController;
use WebFW\Dev\Classes\DevHelper;

/**
 * Class Controller
 *
 * A basic controller used to display developer options.
 *
 * @package WebFW\Dev
 */
abstract class Controller extends HTMLController
{
    /**
     * The realm message which will be displayed to users for authentication
     */
    const REALM_MESSAGE = 'Developer area';

    public function __construct()
    {
        DevHelper::requestAuthentication(static::REALM_MESSAGE);
        parent::__construct();
    }
}
