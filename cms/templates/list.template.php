<?php

use WebFW\Core\Framework;

?>

<script type="text/javascript">
// <![CDATA[
    var sortingDef = <?=$controller->isSortingEnabled() ? $controller->getJSONSortingDef() : 'null'; ?>;
// ]]>
</script>

<?=Framework::runComponent('Filter', '\\WebFW\\CMS\\Components\\', null, $controller); ?>
<?=Framework::runComponent('TreeBreadcrumbs', '\\WebFW\\CMS\\Components\\', null, $controller); ?>
<?=Framework::runComponent('Listing', '\\WebFW\\CMS\\Components\\', null, $controller); ?>
