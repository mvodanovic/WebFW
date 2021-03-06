<?php

namespace WebFW\CMS;

use WebFW\Core\Classes\HTML\Message;
use WebFW\Core\Config;
use WebFW\Core\Request;
use WebFW\Core\Router;
use WebFW\CMS\Classes\LoggedUser;
use WebFW\Core\HTMLController;

abstract class Controller extends HTMLController
{
    const TITLE_SUFFIX = ' - WebFW CMS';

    protected $messages = array();
    protected $isPopup = false;

    protected function __construct()
    {
        parent::__construct();

        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (!LoggedUser::isLoggedIn()) {
            $this->setRedirectUrl(Router::getInstance()->URL(CMSLogin::className(), null, null, false), true);
        }

        if (Request::getInstance()->popup === '1') {
            $this->isPopup = true;
            $this->simpleOutput = true;
        }

        $this->addLinkedCSS('/static/css/webfw/reset.css');
        $this->addLinkedCSS('/static/css/webfw/formalize.css');
        $this->addLinkedCSS('/static/css/webfw/jquery-ui-1.10.3.custom.min.css');
        $this->addLinkedCSS('/static/css/webfw/jquery-ui-timepicker-addon.min.css');
        $this->addLinkedCSS('/static/css/webfw/cms.css');
        $this->addLinkedJS('/static/js/webfw/jquery-1.10.2.min.js');
        $this->addLinkedJS('/static/js/webfw/jquery-ui-1.10.3.custom.min.js');
        $this->addLinkedJS('/static/js/webfw/jquery-ui-timepicker-addon.min.js');
        $this->addLinkedJS('/static/js/webfw/tinymce/tinymce.min.js');
        $this->addLinkedJS('/static/js/webfw/tinymce/jquery.tinymce.min.js');
        $this->addLinkedJS('/static/js/webfw/referencepicker.class.js');
        $this->addLinkedJS('/static/js/webfw/cmspage.class.js');
        $this->addHeadMeta('Content-Type', 'text/html; charset=UTF-8', 'http-equiv');
    }

    protected function init()
    {
        $this->baseTemplate = \WebFW\Core\FW_PATH . '/cms/templates/base';
        $this->setPageTitle();
    }

    protected function afterInit() {}
    protected function afterInitList() {}
    protected function afterInitEdit() {}

    public function getSelectedMenuItem()
    {
        return strtolower(str_replace('\\', '_', static::className()));
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
        $this->pageTitle = ($this->pageTitle === '') ? (static::className()) : $this->pageTitle;
        $this->pageTitle .= static::TITLE_SUFFIX;
        if (Config::get('General', 'projectName') !== null) {
            $this->pageTitle .= ' - ' . Config::get('General', 'projectName');
        }
    }

    public function getRoute($action = null, $params = array())
    {
        if ($this->isPopup === true) {
            $params['popup'] = 1;
        }

        return parent::getRoute($action, $params);
    }

    public function isPopup()
    {
        return $this->isPopup;
    }
}
