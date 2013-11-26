<?php

use WebFW\CMS\Components\Filter;
use WebFW\CMS\Components\Listing;
use WebFW\CMS\Components\TreeBreadcrumbs;
use WebFW\CMS\ListController;
use WebFW\Core\Framework;

/** @var $controller ListController */

?>

<?=Framework::runComponent(Filter::className(), null, $controller); ?>
<?=Framework::runComponent(TreeBreadcrumbs::className(), null, $controller); ?>
<?=Framework::runComponent(Listing::className(), null, $controller); ?>
