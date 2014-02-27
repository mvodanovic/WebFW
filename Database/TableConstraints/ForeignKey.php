<?php

namespace mvodanovic\WebFW\Database\TableConstraints;

use mvodanovic\WebFW\Core\Exception;
use mvodanovic\WebFW\Database\Table;
use mvodanovic\WebFW\Database\TableColumns\Column;

class ForeignKey extends Constraint
{
    const ACTION_DO_NOTHING = 1;
    const ACTION_CASCADE = 2;
    const ACTION_RESTRICT = 3;
    const ACTION_SET_NULL = 4;
    const ACTION_SET_DEFAULT = 5;

    protected $references = array();
    /** @var Table */
    protected $referencedTable = null;
    protected $onUpdate;
    protected $onDelete;

    public function __construct(Table $table, $onUpdate, $onDelete, $name = null)
    {
        if (!$this->actionIsValid($onUpdate)) {
            throw new Exception('Invalid update action supplied for foreign key constraint.');
        }

        if (!$this->actionIsValid($onDelete)) {
            throw new Exception('Invalid delete action supplied for foreign key constraint.');
        }

        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;

        parent::__construct($table, static::TYPE_FOREIGN_KEY, $name);
    }

    public function addReference(Column $localColumn, Column $referencedColumn)
    {
        if ($this->referencedTable === null) {
            $this->referencedTable = $referencedColumn->getTable();
        } else {
            if (get_class($this->referencedTable) !== get_class($referencedColumn->getTable())) {
                throw new Exception('Cannot add a reference to a ' . $referencedColumn->getTable()->getAliasedName()
                    . ' column in a foreign key referencing ' . $this->referencedTable->getAliasedName());
            }
        }

        $this->references[] = array(
            'local' => $localColumn,
            'referenced' => $referencedColumn,
        );
        return $this->addColumn($localColumn);
    }

    public function getReferences()
    {
        return $this->references;
    }

    public function getReferencedTable()
    {
        return $this->referencedTable;
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
            case static::ACTION_CASCADE:
            case static::ACTION_RESTRICT:
            case static::ACTION_SET_NULL:
            case static::ACTION_SET_DEFAULT:
                return true;
        }

        return false;
    }
}
