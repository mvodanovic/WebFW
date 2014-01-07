<?php

namespace WebFW\Framework\CMS\Classes;

use WebFW\Framework\Externals\PHPTemplate;

class Tooltip
{
    const TYPE_NOTICE = 1;
    const TYPE_ERROR = 2;

    protected static $template = 'cms/templates/tooltip.template.php';

    public static function get($message, $type)
    {
        $imageHTML = '';
        $class = '';

        switch ($type) {
            case static::TYPE_NOTICE:
                $imageHTML = '<span class="ui-icon ui-icon-info"></span>';
                $class = 'ui-state-highlight';
                break;
            case static::TYPE_ERROR:
                $imageHTML = '<span class="ui-icon ui-icon-alert"></span>';
                $class = 'ui-state-error';
                break;
        }

        $tpl = new PHPTemplate(\WebFW\Framework\Core\FW_PATH . '/CMS/Templates/tooltip.template.php');
        $tpl->set('message', $message);
        $tpl->set('imageHTML', $imageHTML);
        $tpl->set('class', $class);

        return $tpl->fetch();
    }
}
