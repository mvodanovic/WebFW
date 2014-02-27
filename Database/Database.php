<?php

namespace mvodanovic\WebFW\Database;

use mvodanovic\WebFW\Core\Classes\BaseClass;
use mvodanovic\WebFW\Core\Classes\ClassHelper;

class Database extends BaseClass
{
    public function getCreateQueries()
    {
        $list = ClassHelper::getClasses(Table::className());
        var_dump($list);die;
    }
}
