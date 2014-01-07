<?php

namespace WebFW\Framework\Dev\Controllers;

use WebFW\Framework\Dev\Controller;

class PHPInfo extends Controller
{
    public function execute()
    {
        $this->useTemplate = false;
        $this->simpleOutput = true;
        phpinfo();
    }
}
