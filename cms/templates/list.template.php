<?php

use WebFW\CMS\ListController;
use WebFW\Core\Framework;

/** @var $controller ListController */

?>

<?=Framework::runComponent('WebFW\\CMS\\Components\\Filter', null, $controller); ?>
<?=Framework::runComponent('WebFW\\CMS\\Components\\TreeBreadcrumbs', null, $controller); ?>
<?=Framework::runComponent('WebFW\\CMS\\Components\\Listing', null, $controller); ?>
