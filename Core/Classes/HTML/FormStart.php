<?php

namespace WebFW\Framework\Core\Classes\HTML;

use WebFW\Framework\Core\Classes\HTML\Base\GeneralHTMLItem;
use WebFW\Framework\Core\Route;

class FormStart extends GeneralHTMLItem
{
    protected $method;
    protected $action;

    public function __construct($method = null, $action = null)
    {
        parent::__construct('form');

        $this->method = $method;
        $this->action = $action;
    }

    public function parse()
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
                $hidden = new Input($paramPair[0], Input::INPUT_HIDDEN, $paramPair[1]);
                $hiddenDiv .= $hidden->parse();
            }
            if ($hiddenDiv !== '') {
                $hiddenDiv = '<div class="hidden">' . $hiddenDiv . '</div>';
            }
            $this->setInnerHTML($hiddenDiv);
        }

        $this->setAttribute('method', $this->method);
        $this->setAttribute('action', $this->action);

        return substr(parent::parse(), 0, -7);
    }
}
