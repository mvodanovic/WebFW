<?php

namespace WebFW\CMS;

use WebFW\Core\Classes\HTML\Message;
use WebFW\Core\Router;
use WebFW\CMS\Classes\LoggedUser;
use WebFW\Core\HTMLController;

abstract class Controller extends HTMLController
{
    const TITLE_SUFFIX = ' - WebFW CMS';

    protected $messages = array();

    public function __construct()
    {
        parent::__construct();

        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (!LoggedUser::isLoggedIn()) {
            $this->setRedirectUrl(Router::URL('CMSLogin', null, '\\WebFW\\CMS\\', null, false), true);
        }

        $this->addLinkedCSS('/static/css/webfw/reset.css');
        $this->addLinkedCSS('/static/css/webfw/formalize.css');
        $this->addLinkedCSS('/static/css/webfw/jquery-ui-1.10.3.custom.min.css');
        $this->addLinkedCSS('/static/css/webfw/cms.css');
        $this->addLinkedJS('/static/js/webfw/jquery-1.10.2.min.js');
        $this->addLinkedJS('/static/js/webfw/jquery-ui-1.10.3.custom.min.js');
        $this->addLinkedJS('/static/js/webfw/cms.js');
        $this->addHeadMeta('Content-Type', 'text/html; charset=UTF-8', 'http-equiv');
    }

    protected function init()
    {
        $this->baseTemplate = \WebFW\Config\FW_PATH . '/cms/templates/base';
        $this->setPageTitle();
    }

    protected function afterInit() {}

    public function getSelectedMenuItem()
    {
        return strtolower(str_replace('\\', '_', get_class($this)));
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
    }

    protected function setPageTitle()
    {
        $this->pageTitle = ($this->pageTitle === '') ? ($this->ns . $this->ctl) : $this->pageTitle;
        $this->pageTitle .= static::TITLE_SUFFIX;
    }
}
