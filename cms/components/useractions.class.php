<?php

namespace WebFW\CMS\Components;

use \WebFW\Core\Component;
use \WebFW\CMS\Classes\LoggedUser;
use \WebFW\Core\Classes\HTML\Message;

class UserActions extends Component
{
    public function execute()
    {
        if (!LoggedUser::isLoggedIn()) {
            $this->useTemplate = false;
            return;
        }

        $message = new Message('Welcome, ' . LoggedUser::getInstance()->username);
        $message->addClass('greeting');
        $this->setTplVar('loginMessage', $message->parse());
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'useractions');
        $this->setParam('templateDirectory', \WebFW\Config\FW_PATH . '/cms/templates/components/');
    }
}
