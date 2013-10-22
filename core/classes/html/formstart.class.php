<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseHTMLItem;
use WebFW\Core\Route;

class FormStart extends BaseHTMLItem
{
    protected $tagName = 'form';
    protected $skipInnerHTMLDecoration = true;
    protected $method;
    protected $action;

    public function __construct($method = null, $action = null)
    {
        parent::__construct(null);

        $this->method = $method;
        $this->action = $action;
    }

    public function prepareHTMLChunks()
    {
        $urlParamSeparator = '&';
        if ($this->action instanceof Route) {
            $this->action = $this->action->getURL(false);
        } else {
            if (strpos($this->action, '&amp;') > 0) {
                $urlParamSeparator = '&amp;';
            }
        }
        $actionSplit = explode('?', $this->action, 2);
        $this->action = $actionSplit[0];
        if (array_key_exists(1, $actionSplit)) {
            $params = explode($urlParamSeparator, $actionSplit[1]);
            $hiddenDiv = '';
            foreach ($params as $paramPair) {
                $paramPair = explode('=', $paramPair);
                $paramPair[0] = rawurldecode($paramPair[0]);
                $paramPair[1] = rawurldecode($paramPair[1]);
                $hidden = new Input($paramPair[0], 'hidden', $paramPair[1]);
                $hiddenDiv .= $hidden->parse();
            }
            if ($hiddenDiv !== '') {
                $hiddenDiv = '<div class="hidden">' . $hiddenDiv . '</div>';
            }
            $this->innerHTMLElements[] = $hiddenDiv;
        }

        if ($this->method !== null) {
            $this->addCustomAttribute('method', $this->method);
        }

        if ($this->action !== null) {
            $this->addCustomAttribute('action', $this->action);
        }

        parent::prepareHTMLChunks();
    }

    public function parse()
    {
        return substr(parent::parse(), 0, -7);
    }
}
