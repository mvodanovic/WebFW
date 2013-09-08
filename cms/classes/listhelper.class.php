<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\ArrayAccess;

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
            if (!ArrayAccess::isArray($listItem)) {
                continue;
            }

            if (!ArrayAccess::keyExists($keyColumn, $listItem) || !ArrayAccess::keyExists($valueColumn, $listItem)) {
                continue;
            }

            $newList[$listItem[$keyColumn]] = $listItem[$valueColumn];
        }

        return $newList;
    }
}
