<?php

namespace WebFW\CMS\Controllers;

use WebFW\CMS\Classes\ListHelper;
use WebFW\CMS\ListController;
use WebFW\CMS\DBLayer\ListFetchers\UserTypeControllerPermissions as LFUTCP;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as TGUTCP;
use WebFW\Core\Classes\HTML\Input;
use WebFW\CMS\Classes\EditTab;
use WebFW\Core\Classes\HTML\Select;
use WebFW\CMS\DBLayer\ListFetchers\UserType as LFUserType;

class UserTypeControllerPermissions extends ListController
{
    protected function init()
    {
        $this->pageTitle = 'CMS Controller Permissions for User Types';

        parent::init();

        $this->listFetcher = new LFUTCP();
        $this->tableGateway = new TGUTCP();
    }

    protected function initList()
    {
        parent::initList();

        $this->sort = array(
            'user_type_id' => 'ASC',
        );

        $this->addListColumn('strUserType', 'User Type');
        $this->addListColumn('strController', 'Controller');
        $this->addListColumn('strSelect', 'S', true);
        $this->addListColumn('strInsert', 'I', true);
        $this->addListColumn('strUpdate', 'U', true);
        $this->addListColumn('strDelete', 'D', true);
        $this->addListColumn('strCustom', 'C', true);
    }

    protected function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $userTypeLf = new LFUserType();
        $userTypes = ListHelper::GetKeyValueList($userTypeLf->getList(null, array('user_type_id' => 'ASC')), 'user_type_id', 'caption');

        $tab->addField(
            new Select('user_type_id', $userTypes),
            'User Type',
            'Root user types always have all permissions.',
            true
        );
        $tab->addField(
            new Input('select', 'checkbox'),
            'Select',
            'Ability to see controller\'s item list.',
            false
        );
        $tab->addField(
            new Input('controller', 'text'),
            'Controller',
            'Controller name, without the namespace.',
            true
        );
        $tab->addField(
            new Input('insert', 'checkbox'),
            'Insert',
            'Ability to insert new items using the controller.',
            false
        );
        $tab->addField(
            new Input('namespace', 'text'),
            'Namespace',
            'Full namespace, both starting and ending with a slash.',
            true
        );
        $tab->addField(
            new Input('update', 'checkbox'),
            'Update',
            'Ability to update existing items using the controller.',
            false
        );
        $tab->addField(
            new Input('custom', 'checkbox'),
            'Custom',
            'Unused by default, but can be used for custom access rights.',
            true
        );
        $tab->addField(
            new Input('delete', 'checkbox'),
            'Delete',
            'Ability to delete existing items using the controller.',
            false
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

        $this->addListFilter(new Select('user_type_id', $userTypes), 'User Type');
        $this->addListFilter(new Input('namespace', 'text'), 'Namespace');
        $this->addListFilter(new Input('controller', 'text'), 'Controller');
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            $item['strUserType'] = $item->getUserTypeCaption();
            $item['strController'] = $item->getControllerCaption();
            $item['strSelect'] = $item->checkTypePermissions(TGUTCP::TYPE_SELECT) ? 'S' : '';
            $item['strInsert'] = $item->checkTypePermissions(TGUTCP::TYPE_INSERT) ? 'I' : '';
            $item['strUpdate'] = $item->checkTypePermissions(TGUTCP::TYPE_UPDATE) ? 'U' : '';
            $item['strDelete'] = $item->checkTypePermissions(TGUTCP::TYPE_DELETE) ? 'D' : '';
            $item['strCustom'] = $item->checkTypePermissions(TGUTCP::TYPE_CUSTOM) ? 'C' : '';
        }
    }

    protected function beforeSave()
    {
        $this->tableGateway->permissions = 0;

        if ($this->tableGateway->select === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_SELECT;
        }

        if ($this->tableGateway->insert === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_INSERT;
        }

        if ($this->tableGateway->update === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_UPDATE;
        }

        if ($this->tableGateway->delete === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_DELETE;
        }

        if ($this->tableGateway->custom === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_CUSTOM;
        }
    }

    protected function afterLoad()
    {
        $this->tableGateway->select = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_SELECT);
        $this->tableGateway->insert = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_INSERT);
        $this->tableGateway->update = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_UPDATE);
        $this->tableGateway->delete = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_DELETE);
        $this->tableGateway->custom = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_CUSTOM);
    }
}
