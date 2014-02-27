<?php

namespace mvodanovic\WebFW\Dev\Controllers;

use mvodanovic\WebFW\Dev\Controller;

class PHPInfo extends Controller
{
    public function execute()
    {
        $this->useTemplate = false;
        $this->simpleOutput = true;
        phpinfo();
    }
}
