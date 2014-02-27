<?php

namespace mvodanovic\WebFW\Media\DBLayer\Tables;

use mvodanovic\WebFW\Database\Table;
use mvodanovic\WebFW\Database\TableColumns\IntegerColumn;
use mvodanovic\WebFW\Database\TableColumns\VarcharColumn;
use mvodanovic\WebFW\Database\TableConstraints\PrimaryKey;
use mvodanovic\WebFW\Database\TableConstraints\ForeignKey;
use mvodanovic\WebFW\Database\TableConstraints\Unique;

class ImageAspectRatio extends Table
{
    protected function init()
    {
        $this->setName('webfw_image_aspect_ratio');

        $this->addColumn(IntegerColumn::spawn($this, 'aspect_ratio_id', false)->setDefaultValue(null, true));
        $this->addColumn(VarcharColumn::spawn($this, 'caption', false, 100));
        $this->addColumn(IntegerColumn::spawn($this, 'width', false)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'height', false)->setDefaultValue(null));
        $this->addColumn(VarcharColumn::spawn(
            $this,
            'default_variation',
            true,
            ImageVariation::getInstance()->getColumn('variation')->getPrecision()
        )->setDefaultValue(null));

        $this->addConstraint(PrimaryKey::spawn($this)->addColumn($this->getColumn('aspect_ratio_id')));
        $this->addConstraint(Unique::spawn($this)->addColumn($this->getColumn('caption')));
        $this->addConstraint(ForeignKey::spawn($this, ForeignKey::ACTION_CASCADE, ForeignKey::ACTION_SET_NULL)
                ->addReference(
                    $this->getColumn('default_variation'),
                    ImageVariation::getInstance()->getColumn('variation')
                )
        );
    }
}
