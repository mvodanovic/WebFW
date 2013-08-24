<?php

use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Framework;

?>
<?php if ($depth === 0): ?>
<div class="nav">
<?php endif; ?>

    <ul class="<?=$classes; ?>">
        <?php foreach ($menu->getChildren() as $itemID => $item): ?>
        <li class="nav_item_<?=$itemID; ?>"><?=Link::get($item->caption, $item->getURL()); ?></li>
        <?php endforeach; ?>
    </ul>
    <div class="clear"></div>

    <?php foreach ($menu->getChildren() as $itemID => $item): ?>
    <?=Framework::runComponent('Navigation', '\\WebFW\\CMS\\Components\\', array('depth' => $depth + 1, 'menu' => $item, 'classes' => 'nav_item_' . $itemID), $ownerObject); ?>
    <?php endforeach; ?>

<?php if ($depth === 0): ?>
</div>
<?php endif; ?>

<?php if ($depth === 0): ?>
<script type="text/javascript">
// <![CDATA[
    function select_nav_element(id)
    {
        $('div.nav ul').hide();
        $('div.nav ul.nav_head').show();
        $('div.nav ul li a').removeClass('active');
        while (true) {
            $('div.nav ul.nav_item_' + id).show();
            $('div.nav ul li.nav_item_' + id + ' a').addClass('active').blur();
            if (!(id in navHierarchy) || navHierarchy[id] === null) {
                break;
            }
            id = navHierarchy[id];
        }
    }

    var navHierarchy = <?=$component->getMenuJSON(); ?>;
    select_nav_element('<?=$component->getSelectedMenuItem(); ?>');
// ]]>
</script>
<?php endif; ?>
