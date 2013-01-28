<?php

use \WebFW\Core\Classes\HTML\Link;
use \WebFW\Core\Classes\HTML\Message;

?>
<div class="left">
    <?=Message::get('There are errors present'); ?>
</div>
<div class="clear"></div>

<div class="editor">

    <div class="header">
        <ul class="tabs">
            <li><?=Link::get('Tab 1', null, null, 'active'); ?></li>
            <li><?=Link::get('Tab 2'); ?></li>
            <li><?=Link::get('Tab 3'); ?></li>
        </ul>
        <div class="clear"></div>
    </div>

    <div class="body">
        <table>
            <tr>
                <td class="shrinked">Item 1:</td>
                <td>
                    <input />
                    <span class="tooltip"><img src="<?php echo Link::IMAGE_HELP; ?>" alt="" /><span><img src="<?php echo \WebFW\Core\Classes\HTML\Link::IMAGE_HELP; ?>" alt="" /> This is the tooltip text bla bla bla bla bla bla bla bla bla bla bla bla</span></span>
                </td>
            </tr>
            <tr>
                <td class="shrinked">Item 2:</td>
                <td><input /></td>
            </tr>
            <tr>
                <td class="shrinked">Item 3:</td>
                <td><input /></td>
            </tr>
            <tr>
                <td class="shrinked">Item 4:</td>
                <td>
                    <input />
                    <span class="tooltip"><img src="<?php echo Link::IMAGE_NOTICE; ?>" alt="" /><span class="error"><img src="<?php echo \WebFW\Core\Classes\HTML\Link::IMAGE_NOTICE; ?>" alt="" /> This is the tooltip text bla bla bla bla bla bla bla bla bla bla bla bla</span></span>
                </td>
            </tr>
            <tr>
                <td class="shrinked">Item 5:</td>
                <td>
                    <textarea></textarea>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <?=Link::get('Save', null, \WebFW\Core\Classes\HTML\Link::IMAGE_SAVE); ?>
        <?=Link::get('Apply', null, \WebFW\Core\Classes\HTML\Link::IMAGE_APPLY); ?>
        <?=Link::get('Cancel', null, \WebFW\Core\Classes\HTML\Link::IMAGE_CANCEL); ?>
        <?=Link::get('Delete', null, \WebFW\Core\Classes\HTML\Link::IMAGE_DELETE); ?>
    </div>

</div>
