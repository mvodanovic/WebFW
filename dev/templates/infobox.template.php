<?php
/**
 * @var string|null $title
 * @var string|null $content
 * @var array $dataRows
 */
?>
<div style="border: 2px solid #ff0000; width: 100%;">
    <?php if ($title !== null): ?>
        <div style="color: #ffffff; background-color: #000000; font-family: monospace; font-weight: bold; padding: 2px 5px; width: 100%; float: left;">
            <?=htmlspecialchars($title); ?>
        </div>
        <div class="clear"></div>
    <?php endif; ?>
    <?php if (!empty($dataRows)): ?>
        <table style="color: #ffffff; background-color: #000000; width: 100%; float: left; padding: 2px 5px; text-align: left;">
            <?php foreach ($dataRows as $key => $value): ?>
                <tr>
                    <th style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 5px; text-align: left;">
                        <?=htmlspecialchars($key); ?>:
                    </th>
                    <td style="font-weight: bold; font-family: monospace; padding: 1px 5px;">
                        <?=htmlspecialchars($value); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="clear"></div>
    <?php endif; ?>
    <?php if ($content !== null): ?>
        <?=$content; ?>
    <?php endif; ?>
    <div class="clear"></div>
</div>
