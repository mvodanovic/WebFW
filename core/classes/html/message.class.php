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

        switch ($type) {
            case static::TYPE_NOTICE:
                $this->setImage(static::IMAGE_HELP);
                $this->addClass('notice');
                break;
            case static::TYPE_ERROR:
                $this->setImage(static::IMAGE_NOTICE);
                $this->addClass('error');
                break;
        }
    }

    public static function get($value, $type = null)
    {
        $messageObject = new static($value, $type);
        return $messageObject->parse();
    }
}
