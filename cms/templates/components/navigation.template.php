<?php
    use WebFW\CMS\Components\Navigation;
    use WebFW\CMS\DBLayer\Navigation as TGNavigation;

    /** @var $navList array */
    /** @var $component Navigation */
?>
<div class="nav">
    <?php foreach ($navList as &$navLevel): ?>
        <?php if (!empty($navLevel)): ?>
            <ul data-parent-id="<?=(int) $navLevel[0]->parent_node_id; ?>">
                <?php foreach ($navLevel as &$node): ?>
                <?php /** @var $node TGNavigation */ ?>
                    <li
                        data-name="<?=htmlspecialchars($component->getNodeName($node)); ?>"
                        data-id="<?=$node->node_id; ?>"
                        data-tree="<?=json_encode($node->getTreeIDList()); ?>"
                    >
                        <?=$node->getLink(); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="clear"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<script type="text/javascript">
// <![CDATA[
    $('div.nav ul').hide();
    $('div.nav ul li a').removeClass('ui-state-active').removeClass('ui-state-persist');
    $('div.nav ul[data-parent-id=0]').show();
    selectNavElementByName('<?=$component->getSelectedMenuItem(); ?>', true);
// ]]>
</script>
