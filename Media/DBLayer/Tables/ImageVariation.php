<?php

namespace mvodanovic\WebFW\Media\DBLayer\Tables;

use mvodanovic\WebFW\Database\Table;
use mvodanovic\WebFW\Database\TableColumns\IntegerColumn;
use mvodanovic\WebFW\Database\TableColumns\VarcharColumn;
use mvodanovic\WebFW\Database\TableConstraints\PrimaryKey;
use mvodanovic\WebFW\Database\TableConstraints\ForeignKey;

class ImageVariation extends Table
{
    const TYPE_CROPPED = 1;
    const TYPE_MAX_WIDTH = 2;
    const TYPE_MAX_HEIGHT = 3;
    const TYPE_CONTAIN = 4;
    const TYPE_COVER = 5;

    protected function init()
    {
        $this->setName('webfw_image_variation');

        $this->addColumn(VarcharColumn::spawn($this, 'variation', false, 50)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'width', true)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'height', true)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn($this, 'caption', true, 100));
        $this->addColumn(IntegerColumn::spawn($this, 'type', false)->setDefaultValue(static::TYPE_CROPPED));
        $this->addColumn(IntegerColumn::spawn(
            $this,
            'aspect_ratio_id',
            true,
            ImageAspectRatio::getInstance()->getColumn('aspect_ratio_id')->getPrecision()
        )->setDefaultValue(null));

        $this->addConstraint(PrimaryKey::spawn($this)->addColumn($this->getColumn('variation')));
        $this->addConstraint(ForeignKey::spawn($this, ForeignKey::ACTION_CASCADE, ForeignKey::ACTION_RESTRICT)
            ->addReference(
                $this->getColumn('aspect_ratio_id'),
                ImageAspectRatio::getInstance()->getColumn('aspect_ratio_id')
            )
        );
    }
}
