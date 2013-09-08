<?php

namespace WebFW\CMS\Classes;

use WebFW\Core\Classes\HTML\Base\BaseHTMLItem;
use WebFW\Externals\PHPTemplate;

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
                $imageHTML = '<img alt="" src="' . BaseHTMLItem::IMAGE_HELP . '" />';
                break;
            case static::TYPE_ERROR:
                $imageHTML = '<img alt="" src="' . BaseHTMLItem::IMAGE_NOTICE . '" />';
                $class = 'error';
                break;
        }

        $tpl = new PHPTemplate(\WebFW\Config\FW_PATH . '/cms/templates/tooltip.template.php');
        $tpl->set('message', $message);
        $tpl->set('imageHTML', $imageHTML);
        $tpl->set('class', $class);

        return $tpl->fetch();
    }
}
