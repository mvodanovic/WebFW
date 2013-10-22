<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\ArrayAccess;

class ListHelper
{

    protected function __construct() {}

    public static function GetKeyValueList($list, $keyColumn, $valueColumn, $prefixWithEmptyEntry = false)
    {
        if (!is_array($list)) {
            /// TODO: proper error handling
            return array();
        }

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
                $newList[$listItem[$keyColumn]] = $listItem->$valueColumn();
            } else {
                if (!ArrayAccess::keyExists($keyColumn, $listItem) || !ArrayAccess::keyExists($valueColumn, $listItem)) {
                    continue;
                }

                $newList[$listItem[$keyColumn]] = $listItem[$valueColumn];
            }
        }

        if ($prefixWithEmptyEntry) {
            $newList = static::prefixWithEmptyEntry($newList);
        }

        return $newList;
    }

    public static function prefixWithEmptyEntry($list)
    {
        if (!is_array($list)) {
            /// TODO: proper error handling
            $list = array();
        }

        return array(''  => '') + $list;
    }

    public static function getBooleanList($prefixWithEmptyEntry = false)
    {
        $list = array(
            '1' => 'Yes',
            '0' => 'No',
        );

        if ($prefixWithEmptyEntry) {
            $list = static::prefixWithEmptyEntry($list);
        }

        return $list;
    }
}
