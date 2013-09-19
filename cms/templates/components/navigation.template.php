<div class="nav">
    <?php foreach ($navList as &$navLevel): ?>
        <?php if (!empty($navLevel)): ?>
            <ul data-parent-id="<?=(int) $navLevel[0]->parent_node_id; ?>">
                <?php foreach ($navLevel as &$node): ?>
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
    $('div.nav ul li a').removeClass('active');
    $('div.nav ul[data-parent-id=0]').show();
    select_nav_element_by_name('<?=$component->getSelectedMenuItem(); ?>');
// ]]>
</script>
