<?php

namespace WebFW\CMS\Controllers;

use \WebFW\CMS\Controller;
use \WebFW\CMS\DBLayer\ListFetchers\UserType as LFUserType;
use \WebFW\CMS\DBLayer\UserType as TGUserType;
use \WebFW\Core\Classes\HTML\Input;
use \WebFW\CMS\Classes\EditTab;
use \WebFW\CMS\Classes\ListHelper;

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
        $this->addListColumn('is_root', 'Is Root');
    }

    public function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $userTypeLf = new LFUserType();
        $userTypes = ListHelper::GetKeyValueList($userTypeLf->getList(null, array('user_type_id' => 'ASC')), 'user_type_id', 'caption');
        //unset($userTypeLf);
        //var_dump($userTypeLf->getList(), $userTypes);

        $tab->addField(new Input('caption', null, 'text', null, 'caption'), 'Username', null, false);
        $tab->addField(new Input('is_root', null, 'checkbox', null, 'is_root'), 'Is Root');

        $this->editTabs[] = $tab;
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
//            $item['strUserType'] = $item['user_type_id'];
//            $item['strFullName'] = htmlspecialchars($item['first_name'] . ' ' . $item['last_name']);
        }
    }
}
