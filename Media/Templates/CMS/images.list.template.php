<?php

use WebFW\Framework\CMS\Components\Filter;
use WebFW\Framework\CMS\Components\Listing;
use WebFW\Framework\CMS\Components\TreeBreadcrumbs;
use WebFW\Framework\Core\Framework;

?>
<?=Framework::runComponent(Filter::className()); ?>
<?=Framework::runComponent(TreeBreadcrumbs::className()); ?>
<?=Framework::runComponent(Listing::className(), array(
    'template' => 'images.listing',
    'templateDirectory' => \WebFW\Framework\Core\FW_PATH . '/Media/Templates/CMS/Components',
)); ?>
