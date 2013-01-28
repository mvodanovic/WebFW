<?php

use \WebFW\Core\Framework;
use \WebFW\Core\Classes\HTML\Message;

?>
<?=Framework::runComponent(
    'Paginator',
    '\\WebFW\\Core\\Components\\',
    array(
        'template' => 'paginator',
        'templateDirectory' => \WebFW\Config\FW_PATH . '/cms/templates/components/',
        'page' => $page,
        'totalItemsCount' => $totalCount,
        'itemsPerPage' => $itemsPerPage,
        'ctl' => $controllerName,
        'ns' => $namespace,
        'params' => $paginatorFilter,
    ),
    $component
); ?>

<?php if ($errorMessage !== null): ?>
<div class="left">
    <?=Message::get($errorMessage); ?>
</div>
<?php endif; ?>

<div class="right">
    <?php foreach ($headerButtons as $button): ?>
    <?=$button; ?>
    <?php endforeach; ?>
</div>

<table class="list">
    <thead>
    <tr>
        <?php if ($hasCheckboxes === true): ?>
        <th class="shrinked"><input type="checkbox" /></th>
        <?php endif; ?>
        <?php foreach ($listColumns as &$column): ?>
        <th><?=htmlspecialchars($column['caption']); ?></th>
        <?php endforeach; ?>
        <?php if (!empty($rowButtons)): ?>
        <th class="shrinked">Actions</th>
        <?php endif; ?>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="<?=$columnCount; ?>">
            <?php foreach ($footerButtons as $button): ?>
            <div class="left"><?=$button; ?></div>
            <?php endforeach; ?>
            <div class="right"><span>Total items count: <?=$totalCount; ?></span></div>
        </td>
    </tr>
    </tfoot>
    <tbody>
    <?php foreach ($listData as &$listRow): ?>
        <tr>
            <?php if ($hasCheckboxes === true): ?>
            <td class="shrinked"><input type="checkbox" /></td>
            <?php endif; ?>
            <?php foreach ($listColumns as &$column): ?>
            <td><?=array_key_exists($column['key'], $listRow) ? $listRow[$column['key']] : ''; ?></td>
            <?php endforeach; ?>
            <?php if (!empty($rowButtons)): ?>
            <td class="shrinked">
                <?php foreach ($rowButtons as &$buttonDef): ?>
                <?=$component->getRowButton($buttonDef['button'], $buttonDef['link'], $listRow); ?>
                <?php endforeach; ?>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
