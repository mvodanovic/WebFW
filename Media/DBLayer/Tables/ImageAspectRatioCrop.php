<?php

namespace WebFW\Framework\Media\DBLayer\Tables;

use WebFW\Framework\Database\Table;
use WebFW\Framework\Database\TableColumns\DoublePrecisionColumn;
use WebFW\Framework\Database\TableColumns\IntegerColumn;
use WebFW\Framework\Database\TableColumns\VarcharColumn;
use WebFW\Framework\Database\TableConstraints\PrimaryKey;
use WebFW\Framework\Database\TableConstraints\ForeignKey;

class ImageAspectRatioCrop extends Table
{
    protected function init()
    {
        $this->setName('webfw_image_aspect_ratio_crop');

        $this->addColumn(IntegerColumn::spawn($this, 'image_id', false)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'aspect_ratio_id', false)->setDefaultValue(null));
        $this->addColumn(DoublePrecisionColumn::spawn($this, 'ratio_factor', true)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'x', true)->setDefaultValue(null));
        $this->addColumn(IntegerColumn::spawn($this, 'y', true)->setDefaultValue(null));

        $this->addConstraint(PrimaryKey::spawn($this)
            ->addColumn($this->getColumn('image_id'))
            ->addColumn($this->getColumn('aspect_ratio_id'))
        );
        $this->addConstraint(ForeignKey::spawn($this, ForeignKey::ACTION_CASCADE, ForeignKey::ACTION_CASCADE)
            ->addReference(
                $this->getColumn('image_id'),
                Image::getInstance()->getColumn('image_id')
            )
        );
        $this->addConstraint(ForeignKey::spawn($this, ForeignKey::ACTION_CASCADE, ForeignKey::ACTION_CASCADE)
            ->addReference(
                $this->getColumn('aspect_ratio_id'),
                ImageVariation::getInstance()->getColumn('aspect_ratio_id')
            )
        );
    }
}
