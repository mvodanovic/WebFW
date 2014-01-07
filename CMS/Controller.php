<?php

namespace WebFW\Framework\CMS;

use WebFW\Framework\Core\Classes\HTML\Message;
use WebFW\Framework\Core\Config;
use WebFW\Framework\Core\Request;
use WebFW\Framework\Core\Router;
use WebFW\Framework\CMS\Classes\LoggedUser;
use WebFW\Framework\Core\HTMLController;

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

        $this->addLinkedCSS('/Static/CSS/WebFW/reset.css');
        $this->addLinkedCSS('/Static/CSS/WebFW/formalize.css');
        $this->addLinkedCSS('/Static/CSS/WebFW/jquery-ui-1.10.3.custom.min.css');
        $this->addLinkedCSS('/Static/CSS/WebFW/jquery-ui-timepicker-addon.min.css');
        $this->addLinkedCSS('/Static/CSS/WebFW/cms.css');
        $this->addLinkedJS('/Static/JS/WebFW/jquery-1.10.2.min.js');
        $this->addLinkedJS('/Static/JS/WebFW/jquery-ui-1.10.3.custom.min.js');
        $this->addLinkedJS('/Static/JS/WebFW/jquery-ui-timepicker-addon.min.js');
        $this->addLinkedJS('/Static/JS/WebFW/tinymce/tinymce.min.js');
        $this->addLinkedJS('/Static/JS/WebFW/tinymce/jquery.tinymce.min.js');
        $this->addLinkedJS('/Static/JS/WebFW/referencepicker.class.js');
        $this->addLinkedJS('/Static/JS/WebFW/cmspage.class.js');
        $this->addHeadMeta('Content-Type', 'text/html; charset=UTF-8', 'http-equiv');
    }

    protected function init()
    {
        $this->baseTemplate = 'base';
        $this->baseTemplateDirectory = \WebFW\Framework\Core\FW_PATH . '/CMS/Templates';
        $this->templateDirectory = \WebFW\Framework\Core\FW_PATH . '/CMS/Templates';
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
