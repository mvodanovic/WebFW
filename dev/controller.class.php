<?php

namespace WebFW\Dev;

use WebFW\Core\Config;
use WebFW\Core\Exceptions\ForbiddenException;
use WebFW\Core\HTMLController;
use WebFW\Dev\Classes\AuthenticationHelper;

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
     * @var string The realm message which will be displayed to users for authentication
     */
    protected $realmMessage = 'Developer area';

    public function __construct()
    {
        if (Config::get('Developer', 'devModeEnabled') !== true) {
            $this->authenticate();
        }

        parent::__construct();
    }

    protected function authenticate()
    {
        if (Config::get('Developer', 'authUsername') === null || Config::get('Developer', 'authPassword') === null) {
            throw new ForbiddenException($this->realmMessage);
        }

        AuthenticationHelper::authenticate(
            $this->realmMessage,
            Config::get('Developer', 'authUsername'),
            Config::get('Developer', 'authPassword')
        );
    }
}
