<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseHTMLItem;

class Link extends BaseHTMLItem
{
    protected $tagName = 'a';
    protected $classes = array('button');
    protected $defaultURL = 'javascript:void(0)';

    public function __construct($value, $url = null, $image = null, $class = null)
    {
        parent::__construct($value);

        if ($url === null) {
            $url = $this->defaultURL;
        }
        $this->addCustomAttribute('href', $url);

        $this->image = $image;
        if ($class !== null) {
            $this->classes[] = $class;
        }
    }

    public static function get($value, $url = null, $image = null, $class = null)
    {
        $imageObject = new static($value, $url, $image, $class);
        return $imageObject->parse();
    }
}
