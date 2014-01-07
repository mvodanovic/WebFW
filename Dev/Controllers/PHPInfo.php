<?php

namespace WebFW\Dev\Controllers;

use WebFW\Dev\Controller;

class PHPInfo extends Controller
{
    public function execute()
    {
        $this->useTemplate = false;
        $this->simpleOutput = true;
        phpinfo();
    }
}
