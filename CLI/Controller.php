<?php

namespace WebFW\Framework\CLI;

use WebFW\Framework\Core\Controller as CoreController;

abstract class Controller extends CoreController
{
    protected function __construct()
    {
        parent::__construct();
    }
}