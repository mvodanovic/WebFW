<?php

namespace mvodanovic\WebFW\CMS\Controllers;

use mvodanovic\WebFW\CMS\ListController;
use mvodanovic\WebFW\CMS\DBLayer\ListFetchers\User as LFUser;
use mvodanovic\WebFW\CMS\DBLayer\User as TGUser;
use mvodanovic\WebFW\Core\Classes\HTML\Input;
use mvodanovic\WebFW\Core\Classes\HTML\Textarea;
use mvodanovic\WebFW\Core\Classes\HTML\Select;
use mvodanovic\WebFW\CMS\Classes\EditTab;
use mvodanovic\WebFW\CMS\DBLayer\ListFetchers\UserType as LFUserType;
use mvodanovic\WebFW\CMS\Classes\ListHelper;

class User extends ListController
{
    protected function init()
    {
        $this->pageTitle = 'CMS Users';

        parent::init();

        $this->listFetcher = new LFUser();
        $this->tableGateway = new TGUser();
    }

    protected function initList()
    {
        parent::initList();

        $this->sort = array(
            'user_id' => 'ASC',
        );

        $this->addListColumn('username', 'Username');
        $this->addListColumn('email', 'E-mail');
        $this->addListColumn('strFullName', 'Name');
        $this->addListColumn('strUserType', 'User type');
        $this->addListColumn('strActive', 'Active', true);
    }

    protected function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $userTypeLf = new LFUserType();
        $userTypes = ListHelper::getKeyValueList(
            $userTypeLf->getList(null, array('user_type_id' => 'ASC')),
            'user_type_id',
            'caption'
        );

        $tab->addField(
            new Input('username', 'text'),
            'Username',
            'Username, used for login.'
        );
        $tab->addField(
            new Input('email', 'email'),
            'E-mail',
            'User\'s email, can also be used for login.',
            false
        );
        $tab->addField(
            new Input('first_name', 'text'),
            'First name'
        );
        $tab->addField(
            new Input('last_name', 'text'),
            'Last name',
            null,
            false
        );
        $tab->addField(
            new Input('password', 'password'),
            'Password',
            'This field is used only when changing the user\'s password.'
        );
        $tab->addField(
            new Input('password2', 'password'),
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
        $tab->addField(
            new Select('user_type_id', $userTypes),
            'User Type',
            'TODO: Only root users can work with the root user type.'
        );
        $tab->addField(
            new Input('active', 'checkbox'),
            'Active',
            'Inactive users cannot log in to CMS.',
            true
        );

        $this->editTabs[] = $tab;
    }

    protected function initListFilters()
    {
        $userTypeLf = new LFUserType();
        $userTypes = ListHelper::getKeyValueList(
            $userTypeLf->getList(null, array('user_type_id' => 'ASC')),
            'user_type_id',
            'caption',
            true
        );

        $this->addListFilter(new Select('user_type_id', $userTypes), 'User Type');
        $this->addListFilter(new Input('username', 'text'), 'Username');
        $this->addListFilter(new Input('email', 'email'), 'Email');
        $this->addListFilter(new Select('active', ListHelper::getBooleanList(true)), 'Active');
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            $item['strUserType'] = $item->getUserType() !== null ? $item->getUserType() : '[unknown]';
            $item['strFullName'] = $item->getFullName();
            $item['strActive'] = static::getBooleanPrint($item['active']);
        }
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
