<?php

use WebFW\Core\Framework;
use WebFW\Core\ArrayAccess;

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
        'params' => $filterValues,
    ),
    $component
); ?>

<?php foreach ($messages as &$message): ?>
    <div class="left"><?=$message->parse(); ?></div>
<?php endforeach; ?>

<div class="right">
    <?php foreach ($listActions as &$action): ?>
    <?=$action->getHTMLItem()->parse(); ?>
    <?php endforeach; ?>
</div>

<?php if ($totalCount > 0): ?>
<table class="list">
    <thead>
    <tr>
        <?php if ($hasCheckboxes === true): ?>
        <th class="shrinked"><input type="checkbox" /></th>
        <?php endif; ?>
        <?php foreach ($listColumns as &$column): ?>
        <th<?php if ($column['shrinked'] === true): ?> class="shrinked"<?php endif; ?>>
            <?=htmlspecialchars($column['caption']); ?>
        </th>
        <?php endforeach; ?>
        <?php if (!empty($listRowActions)): ?>
        <th class="shrinked">Actions</th>
        <?php endif; ?>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="<?=$columnCount; ?>">
            <?php foreach ($listMassActions as &$action): ?>
            <div class="left"><?=$action->getButton()->parse(); ?></div>
            <?php endforeach; ?>
            <div class="right"><span>Total items count: <?=$totalCount; ?></span></div>
        </td>
    </tr>
    </tfoot>
    <tbody>
    <?php foreach ($listData as &$listRow): ?>
        <tr<?=$component->getRowMetadata($listRow); ?>>
            <?php if ($hasCheckboxes === true): ?>
            <td class="shrinked"><?=$component->getRowCheckbox($listRow); ?></td>
            <?php endif; ?>
            <?php foreach ($listColumns as &$column): ?>
            <td<?php if ($column['shrinked'] === true): ?> class="shrinked"<?php endif; ?>>
                <?=ArrayAccess::keyExists($column['key'], $listRow) ? $listRow[$column['key']] : ''; ?>
            </td>
            <?php endforeach; ?>
            <?php if (!empty($listRowActions)): ?>
            <td class="shrinked">
                <?php foreach ($listRowActions as &$action): ?>
                <?=$component->getRowButton($action, $listRow); ?>
                <?php endforeach; ?>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
