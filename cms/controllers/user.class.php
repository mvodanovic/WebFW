<?php

namespace WebFW\CMS\Controllers;

use WebFW\CMS\ListController;
use WebFW\CMS\DBLayer\ListFetchers\User as LFUser;
use WebFW\CMS\DBLayer\User as TGUser;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Textarea;
use WebFW\Core\Classes\HTML\Select;
use WebFW\CMS\Classes\EditTab;
use WebFW\CMS\DBLayer\ListFetchers\UserType as LFUserType;
use WebFW\CMS\Classes\ListHelper;

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
        $userTypes = ListHelper::GetKeyValueList(
            $userTypeLf->getList(null, array('user_type_id' => 'ASC')),
            'user_type_id',
            'caption'
        );

        $tab->addField(
            new Input('username', null, 'text', null, 'username'),
            'Username',
            'Username, used for login.'
        );
        $tab->addField(
            new Input('email', null, 'email', null, 'email'),
            'E-mail',
            'User\'s email, can also be used for login.',
            false
        );
        $tab->addField(
            new Input('first_name', null, 'text', null, 'first_name'),
            'First name'
        );
        $tab->addField(
            new Input('last_name', null, 'text', null, 'last_name'),
            'Last name',
            null,
            false
        );
        $tab->addField(
            new Input('password', null, 'password', null, 'password'),
            'Password',
            'This field is used only when changing the user\'s password.'
        );
        $tab->addField(
            new Input('password2', null, 'password', null, 'password2'),
            'Confirm password',
            'This field must match the Password field.',
            false
        );
        $tab->addField(
            new Textarea('address', null, null, 'address'),
            'Address',
            null,
            true,
            2
        );
        $tab->addField(
            new Select('user_type_id', null, $userTypes, null, 'user_type_id'),
            'User Type',
            'TODO: Only root users can work with the root user type.'
        );
        $tab->addField(
            new Input('active', null, 'checkbox', null, 'active'),
            'Active',
            'Inactive users cannot log in to CMS.',
            true
        );

        $this->editTabs[] = $tab;
    }

    protected function initListFilters()
    {
        $userTypeLf = new LFUserType();
        $userTypes = ListHelper::GetKeyValueList(
            $userTypeLf->getList(null, array('user_type_id' => 'ASC')),
            'user_type_id',
            'caption',
            true
        );

        $this->addListFilter(new Select('user_type_id', null, $userTypes, null, 'user_type_id'), 'User Type');
        $this->addListFilter(new Input('username', null, 'text', null, 'username'), 'Username');
        $this->addListFilter(new Input('email', null, 'email', null, 'email'), 'Email');
        $this->addListFilter(new Select('active', null, ListHelper::getBooleanList(true), null, 'active'), 'Active');
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            $item['strUserType'] = $item->getUserType() !== null ? $item->getUserType() : '[unknown]';
            $item['strFullName'] = $item->getFullName();
            $item['strActive'] = static::getBooleanPrint($item['active']);
        }
    }
}
