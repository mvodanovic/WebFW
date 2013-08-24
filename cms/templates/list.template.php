<?php

use WebFW\Core\Framework;

?>

<?=Framework::runComponent('Filter', '\\WebFW\\CMS\\Components\\', null, $controller); ?>
<?=Framework::runComponent('Listing', '\\WebFW\\CMS\\Components\\', null, $controller); ?>
