<?php

use WebFW\Framework\Core\Classes\HTML\Base\BaseFormItem;
use WebFW\Framework\Core\Classes\HTML\FormStart;
use WebFW\Framework\Core\Classes\HTML\Button;

/** @var FormStart $form */
/** @var array $filters */
/** @var Button $submitButton */
/** @var BaseFormItem $formItem */

?>

<div class="filter">
    <?=$form->parse(); ?>
        <ul>
            <?php foreach ($filters as &$filterDef): ?>
            <?php $formItem = $filterDef['formItem']; ?>
            <li>
                <div class="filterItem">
                    <?php if ($formItem->useLabel()): ?>
                    <label>
                    <?php endif; ?>
                        <span class="label"><?=htmlspecialchars($filterDef['label']); ?></span>:
                        <?=$formItem->parse(); ?>
                    <?php if ($formItem->useLabel()): ?>
                    </label>
                    <?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
            <li>
                <?=$submitButton->parse(); ?>
            </li>
        </ul>
    </form>
    <div class="clear"></div>
</div>
<div class="clear"></div>
