<?php

namespace WebFW\Framework\Core\Classes;

/**
 * Class BaseClass
 *
 * Base class for almost all classes in the framework.
 *
 * @package WebFW\Framework\Core
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

    /**
     * Get the class name of the current class without the namespace.
     * @return string The class name
     */
    public static function classShortName()
    {
        $reflector = new \ReflectionClass(static::className());
        return $reflector->getShortName();
    }

    /**
     * Get the class namespace of the current class.
     * @return string The class namespace
     */
    public static function classNamespace()
    {
        $reflector = new \ReflectionClass(static::className());
        return $reflector->getNamespaceName();
    }
}
