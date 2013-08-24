<?php

namespace WebFW\CMS;

use WebFW\Core\HTMLController;
use WebFW\Core\Exception;
use WebFW\Core\Request;
use WebFW\Core\SessionHandler;
use WebFW\Core\Router;
use WebFW\CMS\Classes\LoggedUser;

class CMSLogin extends HTMLController
{
    public function __construct()
    {
        parent::__construct();

        $this->setLinkedCSS('/static/css/reset.css');
        $this->setLinkedCSS('/static/css/formalize.css');
        $this->setLinkedCSS('/static/css/webfw/cms.css');
        $this->setHtmlMeta('Content-Type', 'text/html; charset=UTF-8', 'http-equiv');
    }

    public function execute()
    {
        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (LoggedUser::isLoggedIn()) {
            $this->setRedirectUrl(Router::URL('CMSTest', null, null, null, false));
            return;
        }

        $this->baseTemplate = \WebFW\Config\FW_PATH . '/cms/templates/base';
        $this->template = \WebFW\Config\FW_PATH . '/cms/templates/login';
        $this->pageTitle = 'CMS Login';
        $this->setTplVar('errorMessage', null);
        $this->setTplVar('login', null);
    }

    public function doLogin()
    {
        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (LoggedUser::isLoggedIn()) {
            $this->setRedirectUrl(Router::URL('CMSTest', null, null, null, false));
            return;
        }

        $login = Request::getInstance()->login;
        $password = Request::getInstance()->password;
        $remember = (boolean) Request::getInstance()->remember;

        try {
            LoggedUser::getInstance()->doLogin($login, $password, $remember);
        } catch (Exception $e) {
            $this->execute();
            $this->setTplVar('errorMessage', 'Invalid credentials supplied!');
            $this->setTplVar('login', $login);
            return;
        }

        $returnUrl = SessionHandler::get('returnUrl');
        if ($returnUrl === null) {
            $returnUrl = Router::URL('CMSTest', null, null, null, false);
        }
        $this->setRedirectUrl($returnUrl);
    }

    public function doLogout()
    {
        LoggedUser::getInstance()->doLogout();
        $this->setRedirectUrl(Router::URL('CMSLogin', null, '\\WebFW\\CMS\\', null, false));
    }
}
