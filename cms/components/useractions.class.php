<?php

namespace WebFW\CMS\Components;

use WebFW\CMS\Classes\PermissionsHelper;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
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

        $messageText = 'Welcome, ' . LoggedUser::getInstance()->username;

        /// If the user has permissions to access LoggedUser controller, give him a link.
        if (PermissionsHelper::checkForControllerByName(
            'LoggedUser',
            '\\WebFW\\CMS\\Controllers\\',
            UTCP::TYPE_SELECT
        )) {
            $message = new Link(
                $messageText,
                Router::getInstance()->URL('LoggedUser', null, '\\WebFW\\CMS\\Controllers\\', null, false)
            );

            /// If LoggedUser is the current controller, activate the button.
            if (
                $this->ownerObject->getName() === 'LoggedUser'
                && $this->ownerObject->getNamespace() === '\\WebFW\\CMS\\Controllers\\'
            ) {
                $message->addClass('active');
            }
        }

        /// Else give him only a message.
        else {
            $message = new Message($messageText);
        }
        $button = new Link(
            'Logout',
            Router::getInstance()->URL('CMSLogin', 'doLogout', '\\WebFW\\CMS\\', null, false),
            Link::IMAGE_LOGOUT
        );

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
