<?php

namespace mvodanovic\WebFW\CMS\Controllers;

use mvodanovic\WebFW\CMS\ListController;
use mvodanovic\WebFW\CMS\DBLayer\ListFetchers\UserType as LFUserType;
use mvodanovic\WebFW\CMS\DBLayer\UserType as TGUserType;
use mvodanovic\WebFW\CMS\DBLayer\User as TGUser;
use mvodanovic\WebFW\CMS\DBLayer\Tables\User as TUser;
use mvodanovic\WebFW\Core\Classes\HTML\Input;
use mvodanovic\WebFW\CMS\Classes\EditTab;
use mvodanovic\WebFW\Core\Classes\HTML\ReferencedListPicker;
use mvodanovic\WebFW\Core\Route;

class UserType extends ListController
{
    protected function init()
    {
        $this->pageTitle = 'CMS User Types';

        parent::init();

        $this->listFetcher = new LFUserType();
        $this->tableGateway = new TGUserType();
    }

    protected function initList()
    {
        parent::initList();

        $this->sort = array(
            'user_type_id' => 'ASC',
        );

        $this->addListColumn('caption', 'Caption');
        $this->addListColumn('strIsRoot', 'Is Root', true);
    }

    protected function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $tab->addField(
            new Input('caption', 'text'),
            'Type caption',
            'Caption of the user type.',
            false
        );
        $tab->addField(
            new Input('is_root', 'checkbox'),
            'Is Root',
            "Is this user type the root type.\n Root types have full access rights.",
            true
        );
        $tab->addField(
            new ReferencedListPicker(
                new Route(User::className()),
                TUser::getInstance()->getConstraint('fk_cms_user_user_type_id'),
                TGUser::className()
            ),
            'Users'
        );

        $this->editTabs[] = $tab;
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            $item['strIsRoot'] = static::getBooleanPrint($item['is_root']);
        }
    }
}
