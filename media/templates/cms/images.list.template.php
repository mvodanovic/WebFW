<?php

use WebFW\CMS\Components\Filter;
use WebFW\CMS\Components\Listing;
use WebFW\CMS\Components\TreeBreadcrumbs;
use WebFW\Core\Framework;

?>
<?=Framework::runComponent(Filter::className()); ?>
<?=Framework::runComponent(TreeBreadcrumbs::className()); ?>
<?=Framework::runComponent(Listing::className(), array(
    'template' => 'images.listing',
    'templateDirectory' => \WebFW\Core\FW_PATH . '/media/templates/cms/components/',
)); ?>
