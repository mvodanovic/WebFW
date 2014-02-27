<?php

namespace mvodanovic\WebFW\CMS;

use mvodanovic\WebFW\CMS\DBLayer\ListFetchers\Navigation;
use mvodanovic\WebFW\CMS\DBLayer\Navigation as TGNavigation;
use mvodanovic\WebFW\Core\Classes\HTML\Button;
use mvodanovic\WebFW\Core\Classes\HTML\FormStart;
use mvodanovic\WebFW\Core\Classes\HTML\Input;
use mvodanovic\WebFW\Core\Classes\HTML\Message;
use mvodanovic\WebFW\Core\Exception;
use mvodanovic\WebFW\Core\HTMLController;
use mvodanovic\WebFW\Core\Exceptions\NotFoundException;
use mvodanovic\WebFW\Core\Exceptions\UnauthorizedException;
use mvodanovic\WebFW\Core\Exceptions\ForbiddenException;
use mvodanovic\WebFW\Core\Request;
use mvodanovic\WebFW\Core\SessionHandler;
use mvodanovic\WebFW\Core\Router;
use mvodanovic\WebFW\CMS\Classes\LoggedUser;

class CMSLogin extends HTMLController
{
    protected function __construct()
    {
        parent::__construct();

        $this->addLinkedCSS('/Static/CSS/WebFW/reset.css');
        $this->addLinkedCSS('/Static/CSS/WebFW/formalize.css');
        $this->addLinkedCSS('/Static/CSS/WebFW/cms.css');
        $this->addLinkedCSS('/Static/CSS/WebFW/jquery-ui-1.10.3.custom.min.css');
        $this->addLinkedJS('/Static/JS/WebFW/jquery-1.10.2.min.js');
        $this->addLinkedJS('/Static/JS/WebFW/jquery-ui-1.10.3.custom.min.js');
        $this->addLinkedJS('/Static/JS/WebFW/cmspage.class.js');
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

        $this->baseTemplate = 'base';
        $this->baseTemplateDirectory = \mvodanovic\WebFW\Core\FW_PATH . '/CMS/Templates';
        $this->template = 'login';
        $this->templateDirectory = \mvodanovic\WebFW\Core\FW_PATH . '/CMS/Templates';
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
        $loginButton = new Button(null, Button::BUTTON_SUBMIT, $options);

        $loginForm = new FormStart('post', Router::getInstance()->URL(static::className(), 'doLogin'));

        $usernameField = new Input('login', Input::INPUT_TEXT, $username);
        $passwordField = new Input('password', Input::INPUT_PASSWORD);
        $rememberMeField = new Input('remember', Input::INPUT_CHECKBOX);

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
        $this->setRedirectUrl(Router::getInstance()->URL(static::className(), null, null, false));
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
            if ($node->controller !== null && class_exists($node->controller)) {
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
