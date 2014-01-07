<?php

namespace WebFW\Framework\CMS\Components;

use WebFW\Framework\CMS\Classes\PermissionsHelper;
use WebFW\Framework\CMS\CMSLogin;
use WebFW\Framework\CMS\DBLayer\UserTypeControllerPermissions as UTCP;
use WebFW\Framework\Core\Classes\HTML\Link;
use WebFW\Framework\Core\Component;
use WebFW\Framework\CMS\Classes\LoggedUser;
use WebFW\Framework\CMS\Controllers\LoggedUser as LoggedUserCtl;
use WebFW\Framework\Core\Classes\HTML\Message;
use WebFW\Framework\Core\Router;

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
            LoggedUserCtl::className(),
            UTCP::TYPE_SELECT
        )) {
            $url = Router::getInstance()->URL(LoggedUserCtl::className(), null, null, false);
            $options = array(
                'icons' => array('primary' => 'ui-icon-person'),
                'label' => $messageText,
            );
            $message = new Link(null, $url, $options);

            /// If LoggedUser is the current controller, activate the button.
            if ($this->ownerObject instanceof LoggedUserCtl) {
                $message->addClass('ui-state-active');
                $message->addClass('ui-state-persist');
            }
        }

        /// Else give him only a message.
        else {
            $message = new Message($messageText);
        }

        $url = Router::getInstance()->URL(CMSLogin::className(), 'doLogout', null, false);
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
        $this->setParam('templateDirectory', \WebFW\Framework\Core\FW_PATH . '/CMS/Templates/Components');
    }
}
