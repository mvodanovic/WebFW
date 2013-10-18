<?php

namespace WebFW\CMS\Controllers;

use WebFW\CMS\ItemController;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Textarea;
use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\Classes\LoggedUser as LUClass;

class LoggedUser extends ItemController
{
    protected function init()
    {
        $this->pageTitle = 'My Settings';

        parent::init();

        $this->tableGateway = LUClass::getInstance()->getLoggedUser();
    }

    public function getPrimaryKeyValues($keepPrefix = true)
    {
        return $this->tableGateway->getPrimaryKeyValues($keepPrefix);
    }

    protected function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $tab->addField(
            new Input('username', null, 'text'),
            'Username',
            'Username, used for login.'
        );
        $tab->addField(
            new Input('email', null, 'email'),
            'E-mail',
            'User\'s email, can also be used for login.',
            false
        );
        $tab->addField(
            new Input('first_name', null, 'text'),
            'First name'
        );
        $tab->addField(
            new Input('last_name', null, 'text'),
            'Last name',
            null,
            false
        );
        $tab->addField(
            new Input('password', null, 'password'),
            'Password',
            'This field is used only when changing the user\'s password.'
        );
        $tab->addField(
            new Input('password2', null, 'password'),
            'Confirm password',
            'This field must match the Password field.',
            false
        );
        $tab->addField(
            new Textarea('address'),
            'Address',
            null,
            true,
            2
        );

        $this->editTabs[] = $tab;
    }

    public function validateData()
    {
        parent::validateData();

        if ($this->tableGateway->password !== null) {
            if ($this->tableGateway->password2 !== $this->tableGateway->password) {
                $this->addValidationError('password2', 'Passwords don\'t match');
            }
        }
    }

    protected function beforeSave()
    {
        parent::beforeSave();

        if ($this->tableGateway->password !== null) {
            $this->tableGateway->setPassword($this->tableGateway->password);
        }
    }
}
