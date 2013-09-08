<?php

namespace WebFW\CMS\Components;

use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Component;
use WebFW\CMS\Classes\LoggedUser;
use WebFW\Core\Classes\HTML\Message;
use WebFW\Core\Router;

class UserActions extends Component
{
    public function execute()
    {
        if (!LoggedUser::isLoggedIn()) {
            $this->useTemplate = false;
            return;
        }

        $message = new Message('Welcome, ' . LoggedUser::getInstance()->username);
        $button = new Link('Logout', Router::URL('CMSLogin', 'doLogout', '\\WebFW\\CMS\\'), Link::IMAGE_LOGOUT);

        $this->setTplVar('loginMessage', $message->parse());
        $this->setTplVar('logoutButton', $button->parse());
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'useractions');
        $this->setParam('templateDirectory', \WebFW\Config\FW_PATH . '/cms/templates/components/');
    }
}
