<?php

namespace WebFW\Database;

use WebFW\Core\Classes\BaseClass;
use WebFW\Core\Classes\ClassHelper;

class Database extends BaseClass
{
    public function getCreateQueries()
    {
        $list = ClassHelper::getClasses(Table::className());
        var_dump($list);die;
    }
}
