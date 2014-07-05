<?php

namespace mvodanovic\WebFW\CLI;

use mvodanovic\WebFW\Core\Classes\BaseClass;

abstract class Script extends BaseClass
{
    const COMMAND = null;

    abstract public function execute();
}
