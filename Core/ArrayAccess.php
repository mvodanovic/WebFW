<?php

namespace WebFW\Core;

use WebFW\Core\Classes\BaseClass;

abstract class ArrayAccess extends BaseClass implements \ArrayAccess
{
    public static function keyExists($key, $object)
    {
        if (is_array($object)) {
            return array_key_exists($key, $object);
        } elseif ($object instanceof ArrayAccess) {
            return array_key_exists($key, $object->getValues(true));
        } elseif (is_object($object)) {
            return property_exists($object, $key);
        } else {
            return false;
        }
    }

    public static function isArray($object)
    {
        if (is_array($object)) {
            return true;
        } elseif ($object instanceof ArrayAccess) {
            return true;
        } else {
            return false;
        }
    }

    abstract function getValues();
}
