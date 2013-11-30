<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\ArrayAccess;

/**
 * Class ListHelper
 *
 * Helper class containing functions with common operations on lists.
 *
 * @package WebFW\CMS
 */
class ListHelper
{
    /**
     * Converts a list from ($key => $value) format to ('key' => $key, 'value' => $value) format.
     * Converted list has fixed keys, 'key' and 'value'.
     *
     * @param array $list Input list
     * @param bool $prefixWithEmptyEntry If set to true, the converted list will have an empty element prefixed
     * @return array Converted list
     */
    public static function getKeyValueListFromKeyValuePairs(array $list, $prefixWithEmptyEntry = false)
    {
        $newList = array();
        foreach ($list as $key => $value) {
            $newList[] = array('key' => $key, 'value' => $value);
        }

        if ($prefixWithEmptyEntry === true) {
            $newList = static::prefixWithEmptyEntry($newList);
        }

        return $newList;
    }

    /**
     * Converts a plain list to a list  ('key' => $key, 'value' => $value) format.
     * Both key and value values are the same and containt the value of the input list element.
     *
     * @param array $list Input list
     * @param bool $prefixWithEmptyEntry If set to true, the converted list will have an empty element prefixed
     * @return array Converted list
     */
    public static function toKeyValueList(array $list, $prefixWithEmptyEntry = false)
    {
        $newList = array();
        foreach ($list as &$listItem) {
            $newList[] = array(
                'key' => $listItem,
                'value' => $listItem,
            );
        }

        if ($prefixWithEmptyEntry) {
            $newList = static::prefixWithEmptyEntry($newList);
        }

        return $newList;
    }

    /**
     * Converts a list whose values are lists of key-value pairs to a list with
     * ('key' => $key, 'value' => $value) format. Primarily used for converting ListFetcher result lists.
     *
     * @param array $list Input list
     * @param string $keyColumn Name of the key column in the input list
     * @param string $valueColumn Name of the value column in the input list
     * @param bool $prefixWithEmptyEntry If set to true, the converted list will have an empty element prefixed
     * @return array Converted list
     */
    public static function getKeyValueList(array $list, $keyColumn, $valueColumn, $prefixWithEmptyEntry = false)
    {
        if (!is_string($keyColumn) || !is_string($valueColumn)) {
            /// TODO: proper error handling
            return array();
        }

        $newList = array();
        foreach ($list as &$listItem) {
            if (!ArrayAccess::isArray($listItem)) {
                continue;
            }

            if (is_object($listItem) && method_exists($listItem, $valueColumn)) {
                $newList[] = array(
                    'key' => $listItem[$keyColumn],
                    'value' => $listItem->$valueColumn(),
                );
            } else {
                if (!ArrayAccess::keyExists($keyColumn, $listItem) || !ArrayAccess::keyExists($valueColumn, $listItem)) {
                    continue;
                }

                $newList[] = array(
                    'key' => $listItem[$keyColumn],
                    'value' => $listItem[$valueColumn],
                );
            }
        }

        if ($prefixWithEmptyEntry) {
            $newList = static::prefixWithEmptyEntry($newList);
        }

        return $newList;
    }

    /**
     * Prefixes the input list with an empty key-value list.
     *
     * @param array $list Input list
     * @return array Prefixed list
     */
    public static function prefixWithEmptyEntry(array $list)
    {
        array_unshift($list, array('key' => '', 'value' => ''));
        return $list;
    }

    /**
     * Creates a key-value list representing boolean values.
     *
     * @param bool $prefixWithEmptyEntry If set to true, the converted list will have an empty element prefixed
     * @return array The boolean list
     */
    public static function getBooleanList($prefixWithEmptyEntry = false)
    {
        $list = array(
            array(
                'key' => true,
                'value' => 'Yes',
            ),
            array(
                'key' => false,
                'value' => 'No',
            ),
        );

        if ($prefixWithEmptyEntry) {
            $list = static::prefixWithEmptyEntry($list);
        }

        return $list;
    }

    protected function __construct() {}
    private function __clone() {}
}
