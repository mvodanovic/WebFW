<?php

namespace WebFW\Media\DBLayer\Tables;

use WebFW\Database\Table;
use WebFW\Database\TableColumns\IntegerColumn;
use WebFW\Database\TableColumns\VarcharColumn;
use WebFW\Database\TableConstraints\PrimaryKey;

class Image extends Table
{
    const TYPE_EXTERNAL = 1;
    const TYPE_INTERNAL = 2;

    const EXT_JPG = 1;
    const EXT_PNG = 2;
    const EXT_GIF = 3;

    protected function init()
    {
        $this->setName('webfw_image');

        $this->addColumn(IntegerColumn::spawn($this, 'image_id', false)->setDefaultValue(null, true));
        $this->addColumn(IntegerColumn::spawn($this, 'image_type_id', false)->setDefaultValue(static::TYPE_EXTERNAL));
        $this->addColumn(IntegerColumn::spawn($this, 'extension', false)->setDefaultValue(static::EXT_JPG));
        $this->addColumn(VarcharColumn::spawn($this, 'caption', true, 50));
        $this->addColumn(VarcharColumn::spawn($this, 'author', true, 50));
        $this->addColumn(VarcharColumn::spawn($this, 'uri', true, 500));
        $this->addColumn(IntegerColumn::spawn($this, 'width', true)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'height', true)->setDefaultValue(null));

        $this->addConstraint(PrimaryKey::spawn($this)->addColumn($this->getColumn('image_id')));
    }
}
