<?php

namespace WebFW\CMS;

use WebFW\CMS\DBLayer\ListFetchers\Navigation;
use WebFW\Core\Exception;
use WebFW\Core\HTMLController;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Core\Exceptions\UnauthorizedException;
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
        $this->addHeadMeta('Content-Type', 'text/html; charset=UTF-8', 'http-equiv');
    }

    public function execute()
    {
        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (LoggedUser::isLoggedIn()) {
            $url = $this->getDefaultURL();
            if ($url === null) {
                throw new Exception('Cannot find any CMS controllers to redirect to');
            }
            $this->setRedirectUrl($url, true);
        }

        $this->baseTemplate = \WebFW\Config\FW_PATH . '/cms/templates/base';
        $this->template = \WebFW\Config\FW_PATH . '/cms/templates/login';
        $this->pageTitle = 'CMS Login' . Controller::TITLE_SUFFIX;
        $this->setTplVar('errorMessage', null);
        $this->setTplVar('login', null);
    }

    public function doLogin()
    {
        LoggedUser::getInstance()->doLoginByAutoloadCookie();
        if (LoggedUser::isLoggedIn()) {
            $url = $this->getDefaultURL();
            if ($url === null) {
                throw new Exception('Cannot find any CMS controllers to redirect to');
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
                    $this->execute();
                    $this->setTplVar('errorMessage', $e->getMessage());
                    $this->setTplVar('login', $login);
                    return;
                default:
                    throw $e;
            }
        }

        $returnUrl = SessionHandler::get('returnUrl');
        if ($returnUrl === null) {
            $returnUrl = $this->getDefaultURL();
            if ($returnUrl === null) {
                throw new Exception('Cannot find any CMS controllers to redirect to');
            }
        }
        $this->setRedirectUrl($returnUrl, true);
    }

    public function doLogout()
    {
        LoggedUser::getInstance()->doLogout();
        $this->setRedirectUrl(Router::URL('CMSLogin', null, '\\WebFW\\CMS\\', null, false));
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
