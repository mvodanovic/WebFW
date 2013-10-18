<?php

namespace WebFW\CMS;

use WebFW\CMS\DBLayer\ListFetchers\Navigation;
use WebFW\CMS\DBLayer\Navigation as TGNavigation;
use WebFW\Core\Classes\HTML\Button;
use WebFW\Core\Classes\HTML\FormStart;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Message;
use WebFW\Core\Exception;
use WebFW\Core\HTMLController;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Core\Exceptions\UnauthorizedException;
use WebFW\Core\Exceptions\ForbiddenException;
use WebFW\Core\Request;
use WebFW\Core\SessionHandler;
use WebFW\Core\Router;
use WebFW\CMS\Classes\LoggedUser;

class CMSLogin extends HTMLController
{
    public function __construct()
    {
        parent::__construct();

        $this->addLinkedCSS('/static/css/webfw/reset.css');
        $this->addLinkedCSS('/static/css/webfw/formalize.css');
        $this->addLinkedCSS('/static/css/webfw/cms.css');
        $this->addLinkedCSS('/static/css/webfw/jquery-ui-1.10.3.custom.min.css');
        $this->addLinkedJS('/static/js/webfw/jquery-1.10.2.min.js');
        $this->addLinkedJS('/static/js/webfw/jquery-ui-1.10.3.custom.min.js');
        $this->addLinkedJS('/static/js/webfw/cms.js');
        $this->addHeadMeta('Content-Type', 'text/html; charset=UTF-8', 'http-equiv');
    }

    public function execute()
    {
        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (LoggedUser::isLoggedIn()) {
            $url = $this->getDefaultURL();
            if ($url === null) {
                throw new ForbiddenException('Cannot find any CMS controllers to redirect to');
            }
            $this->setRedirectUrl($url, true);
        }

        $this->setTemplateVariables(null, null);

        $this->baseTemplate = \WebFW\Core\FW_PATH . '/cms/templates/base';
        $this->template = \WebFW\Core\FW_PATH . '/cms/templates/login';
        $this->pageTitle = 'CMS Login' . Controller::TITLE_SUFFIX;
    }

    protected function setTemplateVariables($username, $errorMessage)
    {
        if ($errorMessage !== null) {
            $errorMessage = new Message($errorMessage, Message::TYPE_ERROR);
        }

        $options = array(
            'icons' => array('primary' => 'ui-icon-key'),
            'label' => 'Login',
        );
        $loginButton = new Button(null, 'submit', $options);

        $loginForm = new FormStart('post', Router::getInstance()->URL('CMSLogin', 'doLogin', '\\WebFW\\CMS\\'));

        $usernameField = new Input('login', 'text', $username);
        $passwordField = new Input('password', 'password');
        $rememberMeField = new Input('remember', 'checkbox');

        $this->setTplVar('errorMessage', $errorMessage);
        $this->setTplVar('loginForm', $loginForm);
        $this->setTplVar('usernameField', $usernameField);
        $this->setTplVar('passwordField', $passwordField);
        $this->setTplVar('rememberMeField', $rememberMeField);
        $this->setTplVar('loginButton', $loginButton);
    }

    public function doLogin()
    {
        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (LoggedUser::isLoggedIn()) {
            $url = $this->getDefaultURL();
            if ($url === null) {
                throw new ForbiddenException('Cannot find any CMS controllers to redirect to');
            }
            $this->setRedirectUrl($url, true);
        }

        $login = Request::getInstance()->login;
        $password = Request::getInstance()->password;
        $remember = (boolean) Request::getInstance()->remember;

        try {
            LoggedUser::getInstance()->doLogin($login, $password, $remember);
        } catch (Exception $e) {
            switch (true) {
                case $e instanceof NotFoundException:
                case $e instanceof UnauthorizedException:
                case $e instanceof ForbiddenException:
                    $this->execute();
                    $this->setTemplateVariables($login, $e->getMessage());
                    return;
                default:
                    throw $e;
            }
        }

        $returnUrl = SessionHandler::get('returnUrl');
        if ($returnUrl === null) {
            $returnUrl = $this->getDefaultURL();
            if ($returnUrl === null) {
                throw new ForbiddenException('Cannot find any CMS controllers to redirect to');
            }
        }
        $this->setRedirectUrl($returnUrl, true);
    }

    public function doLogout()
    {
        LoggedUser::getInstance()->doLogout();
        $this->setRedirectUrl(Router::getInstance()->URL('CMSLogin', null, '\\WebFW\\CMS\\', null, false));
    }

    protected function getDefaultURL($parentNodeID = null, Navigation $listFetcher = null)
    {
        $filter = array(
            'parent_node_id' => $parentNodeID,
            'active' => true,
        );
        $sort = array(
            'order_id' => 'ASC',
        );

        if ($listFetcher === null) {
            $listFetcher = new Navigation();
        }

        foreach ($listFetcher->getList($filter, $sort) as $node) {
            /** @var $node TGNavigation */
            if ($node->controller !== null && class_exists($node->namespace . $node->controller)) {
                return $node->getURL();
            }

            $url = $this->getDefaultURL($node->node_id, $listFetcher);
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

}
