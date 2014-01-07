<?php

use WebFW\Framework\Dev\Profiler;

?>
<table style="color: #ffffff; background-color: #000000; width: 100%; float: left; padding: 2px 5px; text-align: left;">
    <thead>
        <tr style="border-bottom: 1px solid #ffffff;">
            <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                Time
            </td>
            <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                Memory
            </td>
            <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                DB Queries
            </td>
            <td style="font-weight: normal; font-family: monospace; padding: 1px 20px; text-align: left;">
                Description
            </td>
        </tr>
    </thead>
    <tfoot>
        <tr style="border-top: 1px solid #ffffff;">
            <td colspan="4" style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 5px; text-align: left;">
                <div>
                    Total time:
                    <span style="font-weight: bold;">
                        <?=Profiler::getInstance()->getTotalTime() . ' seconds'; ?>
                    </span>
                </div>
                <div>
                    Peak memory usage:
                    <span style="font-weight: bold;">
                        <?=Profiler::getInstance()->getPeakMemoryUsage() . ' Bytes (' . Profiler::getInstance()->getPeakMemoryUsage() / 1024 . ' KiB)'; ?>
                    </span>
                </div>
            </td>
        </tr>
    </tfoot>
    <tbody>
        <?php foreach (Profiler::getInstance()->getMoments() as $momentDef): ?>
            <tr>
                <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                    <?=$momentDef['time']; ?> s
                </td>
                <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                    <?=$momentDef['memory']; ?> B
                </td>
                <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                    <?=$momentDef['queryCount']; ?>
                </td>
                <td style="font-weight: normal; font-family: monospace; padding: 1px 20px; text-align: left;">
                    <?=htmlspecialchars($momentDef['description']); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (Profiler::getInstance()->getQueryCount() > 0): ?>
    <table style="color: #ffffff; background-color: #000000; width: 100%; float: left; padding: 2px 5px; text-align: left;">
        <caption style="color: #ffffff; background-color: #000000; font-weight: bold; text-align: left; padding: 1px 5px; border-top: 2px solid #ff0000;">Queries executed</caption>
        <thead>
        <tr style="border-bottom: 1px solid #ffffff;">
            <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                No
            </td>
            <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                Time
            </td>
            <td style="font-weight: normal; font-family: monospace; padding: 1px 20px; text-align: left;">
                Query
            </td>
        </tr>
        </thead>
        <tbody>
        <?php foreach (Profiler::getInstance()->getQueries() as $i => $queryDef): ?>
            <tr>
                <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                    <?=$i+1; ?>
                </td>
                <td style="font-weight: normal; font-family: monospace; width: 5px; white-space: nowrap; padding: 1px 20px; text-align: right;">
                    <?=$queryDef['time']; ?> s
                </td>
                <td style="font-weight: normal; font-family: monospace; padding: 1px 20px; text-align: left;">
                    <?=htmlspecialchars($queryDef['query']); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
