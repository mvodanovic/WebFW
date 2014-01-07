<?php

namespace WebFW\Framework\Database;

use WebFW\Framework\Core\Classes\BaseClass;
use WebFW\Framework\Core\Classes\ClassHelper;

class Database extends BaseClass
{
    public function getCreateQueries()
    {
        $list = ClassHelper::getClasses(Table::className());
        var_dump($list);die;
    }
}
