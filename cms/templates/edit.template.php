<?php

use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Classes\HTML\Message;

?>

<div class="left">
    <?=Message::get('There are errors present'); ?>
</div>
<div class="clear"></div>

<?=$controller->getEditFormHTML(); ?>
    <div class="editor">

        <?php if (count($editTabs) > 1): ?>
            <div class="header">
                <ul class="tabs">
                    <?php foreach ($editTabs as $i => &$tab): ?>
                    <li><?=$tab->getButton($i === 0); ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="clear"></div>
            </div>
        <?php endif; ?>

        <!-- div class="body">
            <table>
                <tr>
                    <td class="shrinked">Item 1:</td>
                    <td>
                        <input />
                        <span class="tooltip"><img src="<?php echo Link::IMAGE_HELP; ?>" alt="" /><span><img src="<?php echo \WebFW\Core\Classes\HTML\Link::IMAGE_HELP; ?>" alt="" /> This is the tooltip text bla bla bla bla bla bla bla bla bla bla bla bla</span></span>
                    </td>
                </tr>
                <tr>
                    <td class="shrinked">Item 2:</td>
                    <td><input /></td>
                </tr>
                <tr>
                    <td class="shrinked">Item 3:</td>
                    <td><input /></td>
                </tr>
                <tr>
                    <td class="shrinked">Item 4:</td>
                    <td>
                        <input />
                        <span class="tooltip"><img src="<?php echo Link::IMAGE_NOTICE; ?>" alt="" /><span class="error"><img src="<?php echo \WebFW\Core\Classes\HTML\Link::IMAGE_NOTICE; ?>" alt="" /> This is the tooltip text bla bla bla bla bla bla bla bla bla bla bla bla</span></span>
                    </td>
                </tr>
                <tr>
                    <td class="shrinked">Item 5:</td>
                    <td>
                        <textarea></textarea>
                    </td>
                </tr>
            </table>
        </div -->

        <?php foreach ($editTabs as &$tab): ?>
            <?php if ($tab->getHiddenFieldCount() > 0): ?>
                <div class="hidden">
                    <?php foreach ($tab->getHiddenFields() as $formItem): ?>
                        <?=$formItem->parse(); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="body" data-tab-id=<?=htmlspecialchars($tab->getID()); ?>>
                <?php if ($tab->getFieldCount() > 0): ?>
                    <table>
                        <?php foreach ($tab->getFields() as $fieldRow): ?>
                        <tr>
                            <?php foreach ($fieldRow as &$field): ?>
                                <td
                                    <?php if ($field['colspan'] > 1): ?> colspan="<?=$field['colspan']; ?>"<?php endif; ?>
                                    <?php if ($field['rowspan'] > 1): ?> rowspan="<?=$field['rowspan']; ?>"<?php endif; ?>
                                    <?php if ($field['rowspanFix'] === true): ?> class="rowspan_fix"<?php endif; ?>
                                >
                                    <label<?php if($field['formItem']->getID() !== null): ?> for="<?=$field['formItem']->getID(); ?>"<?php endif; ?>>
                                        <?=htmlspecialchars($field['label']); ?>:
                                    </label><br \>
                                    <?=$field['formItem']->parse(); ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="footer">
            <div class="left">
            <?php foreach ($editActions as &$action): ?>
                <?php if (!$action->isRightAligned()): ?>
                    <?=$action->getHTMLItem()->parse(); ?>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>
            <div class="right">
                <?php foreach ($editActions as &$action): ?>
                    <?php if ($action->isRightAligned()): ?>
                        <?=$action->getHTMLItem()->parse(); ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="clear"></div>
            <?//=Link::get('Save', null, \WebFW\Core\Classes\HTML\Link::IMAGE_SAVE); ?>
            <?//=Link::get('Apply', null, \WebFW\Core\Classes\HTML\Link::IMAGE_APPLY); ?>
            <?//=Link::get('Cancel', null, \WebFW\Core\Classes\HTML\Link::IMAGE_CANCEL); ?>
            <?//=Link::get('Delete', null, \WebFW\Core\Classes\HTML\Link::IMAGE_DELETE); ?>
        </div>

    </div>
</form>
