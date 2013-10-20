<?php

use WebFW\Core\Classes\HTML\FormStart;
use WebFW\Core\Classes\HTML\Button;

/** @var $form FormStart */
/** @var $filters array */
/** @var $submitButton Button */

?>

<div class="filter">
    <?=$form->parse(); ?>
        <ul>
            <?php foreach ($filters as &$filterDef): ?>
            <li>
                <div class="filterItem">
                    <?php if ($filterDef['formItem']->useLabel()): ?>
                    <label>
                    <?php endif; ?>
                        <span class="label"><?=htmlspecialchars($filterDef['label']); ?></span>:
                        <?=$filterDef['formItem']->parse(); ?>
                    <?php if ($filterDef['formItem']->useLabel()): ?>
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
