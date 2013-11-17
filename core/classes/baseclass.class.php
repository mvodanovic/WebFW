<?php

namespace WebFW\Core\Classes;

/**
 * Class BaseClass
 *
 * Base class for almost all classes in the framework.
 *
 * @package WebFW\Core
 */
abstract class BaseClass
{
    /**
     * Get the full class name of the current class, including namespace.
     * @return string The class name
     */
    public static function className()
    {
        return get_called_class();
    }
}
