<?php

namespace mvodanovic\WebFW\Core\Classes\HTML;

use mvodanovic\WebFW\Core\Classes\HTML\Base\GeneralHTMLItem;

class Message extends GeneralHTMLItem
{
    const TYPE_NOTICE = 1;
    const TYPE_ERROR = 2;

    public function __construct($value, $type = null)
    {
        parent::__construct('div');

        $this->addClass('message ui-widget ui-widget-content ui-corner-all');

        $innerHTML = '';
        switch ($type) {
            case static::TYPE_NOTICE:
                $innerHTML .= '<span class="ui-icon ui-icon-info"></span>';
                $this->addClass('ui-state-highlight');
                break;
            case static::TYPE_ERROR:
                $innerHTML .= '<span class="ui-icon ui-icon-alert"></span>';
                $this->addClass('ui-state-error');
                break;
        }
        $innerHTML .= '<span>' . htmlspecialchars($value) . '</span>';
        $this->setInnerHTML($innerHTML);
    }
}
