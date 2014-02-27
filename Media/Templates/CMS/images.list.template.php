<?php

use mvodanovic\WebFW\CMS\Components\Filter;
use mvodanovic\WebFW\CMS\Components\Listing;
use mvodanovic\WebFW\CMS\Components\TreeBreadcrumbs;
use mvodanovic\WebFW\Core\Framework;

?>
<?=Framework::runComponent(Filter::className()); ?>
<?=Framework::runComponent(TreeBreadcrumbs::className()); ?>
<?=Framework::runComponent(Listing::className(), array(
    'template' => 'images.listing',
    'templateDirectory' => \mvodanovic\WebFW\Core\FW_PATH . '/Media/Templates/CMS/Components',
)); ?>
