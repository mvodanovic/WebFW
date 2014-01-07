<?php

namespace WebFW\Core\Classes;

/**
 * Class GeneralHelper
 *
 * Class containing some helpful functions for general use.
 *
 * @package WebFW\Core
 */
class GeneralHelper extends BaseClass
{
    /**
     * Converts any variable to a print-friendly string representation.
     *
     * @param mixed $item The item to convert
     * @return string The print-friendly version of the item
     */
    public static function toString($item)
    {
        if ($item === null) {
            $item = '<NULL>';
        } elseif (is_string($item)) {
            $item = '"' . $item . '"';
        } elseif (is_object($item)) {
            $itemArray = array();
            foreach ($item as $key => $value) {
                $itemArray[] = $key . '=' . static::toString($value);
            }
            $item = get_class($item) . '(' . implode(',', $itemArray) . ')';
        } elseif (is_array($item)) {
            $itemArray = array();
            foreach ($item as $key => $value) {
                $itemArray[] = $key . '=' . static::toString($value);
            }
            $item = '[' . implode(',', $itemArray) . ']';
        }

        return $item;
    }
}
