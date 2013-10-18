<?php

namespace WebFW\CMS\Controllers;

use WebFW\CMS\ListController;
use WebFW\CMS\DBLayer\ListFetchers\UserType as LFUserType;
use WebFW\CMS\DBLayer\UserType as TGUserType;
use WebFW\Core\Classes\HTML\Input;
use WebFW\CMS\Classes\EditTab;

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

        $this->editTabs[] = $tab;
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            $item['strIsRoot'] = static::getBooleanPrint($item['is_root']);
        }
    }
}
