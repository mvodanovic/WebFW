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
                    <label>
                        <?=htmlspecialchars($filterDef['label']); ?>:
                        <?=$filterDef['formItem']; ?>
                    </label>
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
