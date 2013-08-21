<?php

namespace WebFW\CMS\Classes;

class ListHelper
{

    protected function __construct() {}

    public static function GetKeyValueList($list, $keyColumn, $valueColumn)
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
            if (!is_array($listItem)) {
                continue;
            }

            if (!array_key_exists($keyColumn, $listItem) || !array_key_exists($valueColumn, $listItem)) {
                continue;
            }

            $newList[$listItem[$keyColumn]] = $listItem[$valueColumn];
        }

        return $newList;
    }
}
