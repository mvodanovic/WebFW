<?php

namespace WebFW\Core\Classes\HTML;

use \WebFW\Core\Classes\HTML\Base\BaseHTMLItem;

class Message extends BaseHTMLItem
{
    protected $tagName = 'div';
    protected $classes = array('notice');

    public static function get($value)
    {
        $messageObject = new static($value);
        $messageObject->setImage(static::IMAGE_NOTICE);
        return $messageObject->parse();
    }
}
