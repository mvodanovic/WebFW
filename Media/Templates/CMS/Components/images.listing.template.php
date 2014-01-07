<?php

use WebFW\Framework\CMS\Classes\ListAction;
use WebFW\Framework\CMS\Classes\ListMassAction;
use WebFW\Framework\CMS\Components\Listing;
use WebFW\Framework\Core\Classes\HTML\Message;
use WebFW\Framework\Core\Components\Paginator;
use WebFW\Framework\Core\Controller;
use WebFW\Framework\Core\Framework;
use WebFW\Framework\Media\Controllers\CMS\Image;
use WebFW\Framework\Media\DBLayer\Image as TGImage;
use WebFW\Framework\Media\DBLayer\ImageVariation;

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
 * @var Image $controller
 * @var TGImage $image
 */

$controller = Controller::getInstance();
$variation = $controller->getVariationObject();

?>

<?=Framework::runComponent(
    Paginator::className(),
    array(
        'template' => 'paginator',
        'templateDirectory' => \WebFW\Framework\Core\FW_PATH . '/cms/templates/components/',
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

<div class="clear"></div>
<?php if ($variation instanceof ImageVariation): ?>
    <?php foreach ($listData as $image): ?>
        <div class="left" style="border: 1px solid #0033ff; margin: 5px; border-radius: 5px; width: <?=$variation->width + 2; ?>px;">
            <div style="background-color: #6699ff; padding: 5px; white-space: nowrap; overflow: hidden;">
                <?=htmlspecialchars($image->getCaption()); ?>
            </div>
            <div style="width: <?=$variation->width; ?>px; height: <?=$variation->height; ?>px; overflow: hidden;">
                <a href="<?=$image->getURL(); ?>" target="_blank" style="display: block; background-position: center; background-size: cover; background-image: url('<?=$image->getURL($variation->variation); ?>'); width: 100%; height: 100%;">
                </a>
            </div>
            <div style="background-color: #99ccff; padding: 3px;">
                <?php if ($hasCheckboxes === true): ?>
                    <div style="padding: 0 3px;" class="left">
                        <span style="line-height: 35px;"><?=$component->getRowCheckbox($image); ?></span>
                    </div>
                <?php endif; ?>
                <div style="padding: 0 3px;" class="right">
                    <?php foreach ($listRowActions as &$action): ?>
                        <?=$component->getRowButton($action, $image); ?>
                    <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="clear"></div>
    <div style="background-color: #0033ff; color: #ffffff; margin: 0 0.1em; padding: 0.2em 0.4em; vertical-align: middle;">
        <?php foreach ($listMassActions as &$massAction): ?>
            <div class="left"><?=$massAction->getButton()->parse(); ?></div>
        <?php endforeach; ?>
        <div class="right" style="line-height: 35px;"><span>Total items count: <?=$totalCount; ?></span></div>
        <div class="clear"></div>
    </div>
<?php endif; ?>
