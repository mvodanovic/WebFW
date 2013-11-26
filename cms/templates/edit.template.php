<?php

use WebFW\CMS\Classes\EditAction;
use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\ItemController;
use WebFW\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Core\Classes\HTML\Message;

/**
 * @var ItemController $controller
 * @var Message $message
 * @var array $editTabs
 * @var EditTab $tab
 * @var BaseFormItem $formItem
 * @var array $editActions
 * @var EditAction $action
 */

?>
<?php foreach ($controller->getMessages() as $message): ?>
    <div class="left"><?=$message->parse(); ?></div>
<?php endforeach; ?>
<div class="clear"></div>

<?=$controller->getEditFormHTML(); ?>
    <div class="editor">

        <?php if (count($editTabs) > 1): ?>
            <div class="header jquery_ui_buttonset">
                <?php foreach ($editTabs as $i => &$tab): ?>
                    <?=$tab->getButton($i === 0); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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
                                <?php $formItem = $field['formItem']; ?>
                                    <td
                                        <?php if ($field['colspan'] > 1): ?> colspan="<?=$field['colspan']; ?>"<?php endif; ?>
                                        <?php if ($field['rowspan'] > 1): ?> rowspan="<?=$field['rowspan']; ?>"<?php endif; ?>
                                        <?php if ($field['rowspanFix'] === true): ?> class="rowspan_fix"<?php endif; ?>
                                    >
                                        <?php if ($formItem->useLabel()): ?>
                                        <label>
                                        <?php endif; ?>
                                            <span class="label"><?=htmlspecialchars($field['label']); ?></span>:<br />
                                            <?=$formItem->parse(); ?>
                                            <?=array_key_exists('description', $field) ? $field['description'] : ''; ?>
                                            <?=array_key_exists('error', $field) ? $field['error'] : ''; ?>
                                        <?php if ($formItem->useLabel()): ?>
                                        </label>
                                        <?php endif; ?>
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
        </div>

    </div>
</form>
