<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseHTMLItem;

class Message extends BaseHTMLItem
{
    protected $tagName = 'div';
    protected $classes = array('message');

    const TYPE_NOTICE = 1;
    const TYPE_ERROR = 2;

    public function __construct($value, $type = null)
    {
        parent::__construct($value);

        $this->addClass('ui-widget ui-widget-content ui-corner-all');

        switch ($type) {
            case static::TYPE_NOTICE:
                $this->setImage('ui-icon-info');
                $this->addClass('ui-state-highlight');
                break;
            case static::TYPE_ERROR:
                $this->setImage('ui-icon-alert');
                $this->addClass('ui-state-error');
                break;
        }
    }
}
