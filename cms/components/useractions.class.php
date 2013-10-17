<?php

namespace WebFW\CMS\Components;

use WebFW\CMS\Classes\PermissionsHelper;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Component;
use WebFW\CMS\Classes\LoggedUser;
use WebFW\CMS\Controllers\LoggedUser as LoggedUserCtl;
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
            $url = Router::getInstance()->URL('LoggedUser', null, '\\WebFW\\CMS\\Controllers\\', null, false);
            $options = array(
                'icons' => array('primary' => 'ui-icon-person'),
                'label' => $messageText,
            );
            $message = new Link(null, $url, $options);

            /// If LoggedUser is the current controller, activate the button.
            if ($this->ownerObject instanceof LoggedUserCtl) {
                $message->addClass('ui-state-focus');
            }
        }

        /// Else give him only a message.
        else {
            $message = new Message($messageText);
        }

        $url = Router::getInstance()->URL('CMSLogin', 'doLogout', '\\WebFW\\CMS\\', null, false);
        $options = array(
            'icons' => array('primary' => 'ui-icon-power'),
            'label' => 'Logout',
        );
        $button = new Link(null, $url, $options);

        $this->setTplVar('loginMessage', $message->parse());
        $this->setTplVar('logoutButton', $button->parse());
    }

    protected function setDefaultParams()
    {
        parent::setDefaultParams();

        $this->setParam('template', 'useractions');
        $this->setParam('templateDirectory', \WebFW\Core\FW_PATH . '/cms/templates/components/');
    }
}
