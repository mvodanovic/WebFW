<?php

namespace WebFW\CMS\Controllers;

use WebFW\CMS\Controller;
use WebFW\CMS\DBLayer\ListFetchers\UserType as LFUserType;
use WebFW\CMS\DBLayer\UserType as TGUserType;
use WebFW\Core\Classes\HTML\Input;
use WebFW\CMS\Classes\EditTab;

class UserType extends Controller
{
    public function init()
    {
        parent::init();

        $this->listFetcher = new LFUserType();
        $this->tableGateway = new TGUserType();
    }

    public function initList()
    {
        parent::initList();

        $this->sort = array(
            'user_type_id' => 'ASC',
        );

        $this->addListColumn('caption', 'Caption');
        $this->addListColumn('strIsRoot', 'Is Root', true);
    }

    public function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $tab->addField(
            new Input('caption', null, 'text', null, 'caption'),
            'Type caption',
            'Caption of the user type.',
            false
        );
        $tab->addField(
            new Input('is_root', null, 'checkbox', null, 'is_root'),
            'Is Root',
            "Is this user type the root type.\n Root types have full access rights.",
            true
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
