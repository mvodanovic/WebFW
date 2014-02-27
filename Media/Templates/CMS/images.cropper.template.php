<?php

use mvodanovic\WebFW\CMS\Classes\EditTab;
use mvodanovic\WebFW\CMS\Classes\Tooltip;
use mvodanovic\WebFW\Core\Classes\HTML\Button;
use mvodanovic\WebFW\Core\Classes\HTML\FormStart;
use mvodanovic\WebFW\Core\Classes\HTML\Input;
use mvodanovic\WebFW\Core\Classes\HTML\Message;
use mvodanovic\WebFW\Core\Controller;
use mvodanovic\WebFW\Media\Classes\ImageHelper;
use mvodanovic\WebFW\Media\Controllers\CMS\Image as ImageController;
use mvodanovic\WebFW\Media\DBLayer\Image;
use mvodanovic\WebFW\Media\DBLayer\ImageAspectRatio;

$controller = ImageController::getInstance();

/**
 * @var Image $image
 * @var ImageAspectRatio[] $aspectRatios
 * @var ImageController $controller
 * @var Message $message
 */

?>
<?php foreach ($controller->getMessages() as $message): ?>
    <div class="left"><?=$message->parse(); ?></div>
<?php endforeach; ?>
<div class="clear"></div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.cropper').each(function() {
            new ImageCropper(this);
        });
    });
</script>
<?php foreach ($aspectRatios as $ratio): ?>
<div
    class="cropper"
    data-editor-box-width="<?=$controller::MAX_CROP_EDITOR_WIDTH; ?>"
    data-editor-box-height="<?=$controller::MAX_CROP_EDITOR_HEIGHT; ?>"
    data-thumbnail-width="<?=$controller->getCropThumbnailWidth($ratio->getDefaultVariation()); ?>"
    data-thumbnail-height="<?=$controller->getCropThumbnailHeight($ratio->getDefaultVariation()); ?>"
    data-ratio-width="<?=$ratio->width; ?>"
    data-ratio-height="<?=$ratio->height; ?>"
    data-image-width="<?=$image->width; ?>"
    data-image-height="<?=$image->height; ?>"
    data-crop="<?=htmlspecialchars(json_encode(ImageHelper::getCrop($image, $ratio))); ?>"
>
    <?=(new FormStart('POST', Controller::getInstance()->getURL('cropItem')))->parse(); ?>
        <div class="left_column">
            <div class="caption_block">
                <div class="caption left"><?=htmlspecialchars($ratio->getCaption()); ?></div>
                <div class="right"><?=$ratio->width, ':', $ratio->height, ' (', number_format($ratio->width / $ratio->height, 2), ')'; ?></div>
                <div class="clear"></div>
            </div>
            <div>
                <?=(new Input(EditTab::FIELD_PREFIX . 'image_id', Input::INPUT_HIDDEN, $image->image_id))->parse(); ?>
                <?=(new Input(EditTab::FIELD_PREFIX . 'aspect_ratio_id', Input::INPUT_HIDDEN, $ratio->aspect_ratio_id))->parse(); ?>
                <label>x: <?=(new Input(EditTab::FIELD_PREFIX . 'x', Input::INPUT_TEXT))->addClass('x')->parse(); ?></label>
                <label>y: <?=(new Input(EditTab::FIELD_PREFIX . 'y', Input::INPUT_TEXT))->addClass('y')->parse(); ?></label>
                <label>f: <?=(new Input(EditTab::FIELD_PREFIX . 'f', Input::INPUT_TEXT))->addClass('f')->parse(); ?></label>
            </div>
            <div class="thumbnail_block">
                <img class="preview" src="<?=$image->getURL(); ?>" />
            </div>
        </div>
        <div class="full_block">
            <img class="full" src="<?=$image->getURL(); ?>" />
        </div>
        <div class="bottom">
            <?=(new Button(null, Button::BUTTON_SUBMIT, array(
                'icons' => array('primary' => 'ui-icon-disk'),
                'label' => 'Save',
            )))->parse(); ?>
            <?=Tooltip::get('', Tooltip::TYPE_ERROR); ?>
        </div>
        <div class="clear"></div>
    </form>
</div>
<div class="clear"></div>
<?php endforeach; ?>
