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
                <span class="filter">
                    <?php if ($filterDef['label'] !== null): ?>
                    <label<?php if($filterDef['id'] !== null): ?> for="<?=$filterDef['id']; ?>"<?php endif; ?>><?=htmlspecialchars($filterDef['label']); ?>:</label>
                    <?php endif; ?>
                    <?=$filterDef['formItem']; ?>
                </span>
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
