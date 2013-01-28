<?php

use \WebFW\Core\Classes\HTML\Button;
use \WebFW\Core\Classes\HTML\Input;

?>

<div class="filter">
    <form method="get" action="<?=$targetURL; ?>">
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
                <?=Input::get('ctl', $ctl, 'hidden'); ?>
                <?=Input::get('ns', $ns, 'hidden'); ?>
                <?=Input::get('action', $action, 'hidden'); ?>
                <?=Button::get(null, 'Filter', Button::IMAGE_SEARCH, 'submit'); ?>
            </li>
        </ul>
    </form>
    <div class="clear"></div>
</div>
<div class="clear"></div>
