<?php

use WebFW\CMS\Classes\ListAction;
use WebFW\CMS\Classes\ListMassAction;
use WebFW\CMS\Components\Listing;
use WebFW\Core\Classes\HTML\Message;
use WebFW\Core\Framework;
use WebFW\Core\ArrayAccess;

/**
 * @var Listing $component
 * @var array $listData
 * @var array $listColumns
 * @var int $totalCount
 * @var int $columnCount
 * @var int $page
 * @var int $itemsPerPage
 * @var string $controllerName
 * @var array $filterValues
 * @var array $messages
 * @var array $listActions
 * @var array $listRowActions
 * @var array $listMassActions
 * @var bool $hasCheckboxes
 * @var string $sortingDefinitionJSON
 */

?>

<?=Framework::runComponent(
    'WebFW\\Core\\Components\\Paginator',
    array(
        'template' => 'paginator',
        'templateDirectory' => \WebFW\Core\FW_PATH . '/cms/templates/components/',
        'page' => $page,
        'totalItemsCount' => $totalCount,
        'itemsPerPage' => $itemsPerPage,
        'ctl' => $controllerName,
        'params' => $filterValues,
    ),
    $component
); ?>

<?php foreach ($messages as &$message): ?>
    <?php /** @var Message $message */ ?>
    <div class="left"><?=$message->parse(); ?></div>
<?php endforeach; ?>

<div class="right">
    <?php foreach ($listActions as &$action): ?>
        <?php /** @var ListAction $action */ ?>
        <?=$action->getHTMLItem()->parse(); ?>
    <?php endforeach; ?>
</div>

<?php if ($totalCount > 0): ?>
<table class="list" data-sorting-definition="<?=htmlspecialchars($sortingDefinitionJSON); ?>">
    <thead>
    <tr>
        <?php if ($hasCheckboxes === true): ?>
            <th class="shrinked"><input type="checkbox" /></th>
        <?php endif; ?>
        <?php foreach ($listColumns as &$column): ?>
            <?php /** @var array $column */ ?>
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
            <?php /** @var ListMassAction $action */ ?>
            <div class="left"><?=$action->getButton()->parse(); ?></div>
            <?php endforeach; ?>
            <div class="right"><span>Total items count: <?=$totalCount; ?></span></div>
        </td>
    </tr>
    </tfoot>
    <tbody>
    <?php foreach ($listData as &$listRow): ?>
        <?php /** @var array $listRow */ ?>
        <tr<?=$component->getRowMetadata($listRow); ?>>
            <?php if ($hasCheckboxes === true): ?>
                <td class="shrinked"><?=$component->getRowCheckbox($listRow); ?></td>
            <?php endif; ?>
            <?php foreach ($listColumns as &$column): ?>
                <?php /** @var array $column */ ?>
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
