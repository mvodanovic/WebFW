<?php

use mvodanovic\WebFW\CMS\Classes\ListAction;
use mvodanovic\WebFW\CMS\Classes\ListMassAction;
use mvodanovic\WebFW\CMS\Components\Listing;
use mvodanovic\WebFW\Core\Classes\HTML\Message;
use mvodanovic\WebFW\Core\Components\Paginator;
use mvodanovic\WebFW\Core\Framework;
use mvodanovic\WebFW\Core\ArrayAccess;

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
 * @var Message $message
 * @var ListAction $action
 * @var array $column
 * @var ListMassAction $massAction
 * @var array $listRow
 */

?>

<?=Framework::runComponent(
    Paginator::className(),
    array(
        'template' => 'paginator',
        'templateDirectory' => \mvodanovic\WebFW\Core\FW_PATH . '/CMS/Templates/Components',
        'page' => $page,
        'totalItemsCount' => $totalCount,
        'itemsPerPage' => $itemsPerPage,
        'ctl' => $controllerName,
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
<table class="list" data-sorting-definition="<?=htmlspecialchars($sortingDefinitionJSON); ?>">
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
            <?php foreach ($listMassActions as &$massAction): ?>
            <div class="left"><?=$massAction->getButton()->parse(); ?></div>
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
