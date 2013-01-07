<?php

namespace WebFW\Database\TableConstraints;

use \WebFW\Core\Exception;

class ForeignKey extends Constraint
{
    const ACTION_DO_NOTHING = 1;
    const ACTION_UPDATE = 2;
    const ACTION_RESTRICT = 3;
    const ACTION_SET_NULL = 4;
    const ACTION_SET_DEFAULT = 5;

    protected $references;
    protected $onUpdate;
    protected $onDelete;

    public function __construct($columns, $references, $onUpdate, $onDelete, $name = null)
    {
        if (!$this->actionIsValid($onUpdate)) {
            throw new Exception('Invalid update action supplied for foreign key constraint.');
        }

        if (!$this->actionIsValid($onDelete)) {
            throw new Exception('Invalid delete action supplied for foreign key constraint.');
        }

        parent::__construct(static::TYPE_FOREIGN_KEY, $columns, $name);
        $this->references = is_array($references) ? $references : array($references);
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
    }

    public function getReferences()
    {
        return $this->references;
    }

    public function getOnUpdate()
    {
        return $this->onUpdate;
    }

    public function getOnDelete()
    {
        return $this->onDelete;
    }

    protected function actionIsValid($action)
    {
        switch ($action) {
            case static::ACTION_DO_NOTHING:
            case static::ACTION_UPDATE:
            case static::ACTION_RESTRICT:
            case static::ACTION_SET_NULL:
            case static::ACTION_SET_DEFAULT:
                return true;
        }

        return false;
    }
}
